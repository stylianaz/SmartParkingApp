import random
import datetime as dt
import time
# import altair as alt

import ml_logger
import mySQLConnection
import pickle_helpers
import ml_interface
import file_paths
import data_gathering.data_config as data_config
import data_generation.mock_data_classes as mock_data_classes

# generate_mock_data is the root for generating mock data
# there are also various database checks to make sure that all new carparks are registered with the MockDataManager
# provided by ml_scheduler


def generate_mock_data(data_manager):
    check_for_carparks_which_need_generated_data(data_manager)

    ml_logger.log("generating mock data for all relevant carparks")

    insert_recording = "INSERT INTO occupancy (carpark_id, spaces_available, timestamp) VALUES (%s, %s, %s)"
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor()

    for carpark_id in data_manager.registered_carparks:
        recording = data_manager.get_next_recording_from(carpark_id)
        cursor.execute(insert_recording, recording)
    conn.commit()
    cursor.close()
    pickle_helpers.save_data_to_pickle_jar(data_manager,
            file_paths.path_from_root("data", "trackers", "data_generation_manager.bin"))


class MockDataManager(object):
    def __init__(self, tick_time):
        self._carparks = {}
        self.tick_time = tick_time

    @property
    def registered_carparks(self):
        return self._carparks.keys()

    def add_carpark(self, id, capacity, start_point):
        if not self.is_carpark_registered(id):
            self._carparks[id] = mock_data_classes.MockCarpark(capacity, id, start_point, BasicDemandSimulator())

    def num_registered_carparks(self):
        return len(self._carparks)

    def is_carpark_registered(self, id):
        return id in self.registered_carparks

    # goes to each carpark and gets the next simulated recording, pushing the carparks timer forwards
    def get_next_recording_from(self, id):
        carpark = self._carparks[id]
        timestamp = carpark.current_timestamp
        carpark.tick(self.tick_time)
        return id, carpark.spaces_available, timestamp

    # generates back samples for n past days
    def generate_historical_data_for(self, id, days):
        original_timestamp = self._carparks[id].current_timestamp
        self._carparks[id].current_timestamp -= days * 24 * 60 * 60
        all_data = []
        while self._carparks[id].current_timestamp < original_timestamp:
            all_data.append(self.get_next_recording_from(id))
        return all_data


def check_for_carparks_which_need_generated_data(data_manager):
    ml_logger.log("checking whether new carparks which need mock data have been added")

    if not is_check_for_new_carparks_needed(data_manager):
        ml_logger.log("none found")
        return

    query = "SELECT id, capacity FROM places"
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor()
    cursor.execute(query)

    days_of_required_data = data_config.days_of_historical_data
    start_times = time.time()
    for (id, capacity) in cursor:
        if not data_manager.is_carpark_registered(id) and not is_live_data_available_for_carpark(id):
            ml_logger.log(f"registering new carpark with id: {id}, to data generator")
            data_manager.add_carpark(id, capacity, start_times)
            insert_mock_historical_data(data_manager, id, days_of_required_data)
    cursor.close()
    pickle_helpers.save_data_to_pickle_jar(data_manager,
        file_paths.path_from_root("data", "trackers", "data_generation_manager.bin"))


# dynamic carparks are those for which we receive live data
def get_total_dynamic_carparks():
    query = ("SELECT COUNT(*) AS total_carparks FROM ml_datasets "
             "WHERE is_dynamic = 1")
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute(query)
    total_carparks = cursor.fetchone()["total_carparks"]
    cursor.close()
    return total_carparks


def is_check_for_new_carparks_needed(data_manager):
    expected_registered_carparks = ml_interface.get_total_carparks() - get_total_dynamic_carparks()
    return not expected_registered_carparks == data_manager.num_registered_carparks


def is_live_data_available_for_carpark(carpark_id):
    query = ("SELECT is_dynamic FROM ml_datasets "
             "WHERE main_id = %(carpark_id)s")
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute(query, {"carpark_id": carpark_id})

    first_row = cursor.fetchone()
    if first_row is None:
        return False

    to_return = first_row["is_dynamic"]
    cursor.close()
    return to_return


def insert_mock_historical_data(data_manager, id, days_of_required_data):
    insert_recording = "INSERT INTO occupancy (carpark_id, spaces_available, timestamp) VALUES (%s, %s, %s)"
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor()

    data = data_manager.generate_historical_data_for(id, days_of_required_data)
    cursor.executemany(insert_recording, data)

    conn.commit()
    cursor.close()


class BasicDemandSimulator(mock_data_classes.DemandSimulator):
    def __init__(self):
        super().__init__(list(map(lambda _: get_random_demand_source(), [None] * 4)))


def get_random_demand_source():
    all = [BigEvent, WeeklyEvent, WorkMonday, WorkTuesday, WorkWednesday, WorkThursday,
    WorkFriday, DayTimeCommercial, EarlyMorningResidential, LateNightResidential]
    return all[random.randrange(0, len(all))]()


# these events get randomly added to carparks to simulate demand with multiple periodic sources
# adding these non-randomly could allow the simulation of occupancy for specific kinds of carparks
class BigEvent(mock_data_classes.DemandSource):
    def __init__(self):
        super().__init__(1000, "year", dt.timedelta(hours=3))


class WeeklyEvent(mock_data_classes.DemandSource):
    def __init__(self):
        super().__init__(50, "week", dt.timedelta(hours=2))


class WorkMonday(mock_data_classes.DemandSource):
    def __init__(self):
        super().__init__(30, "week", dt.timedelta(hours=10), period_start=dt.timedelta(hours=8))


class WorkTuesday(mock_data_classes.DemandSource):
    def __init__(self):
        super().__init__(30, "week", dt.timedelta(hours=10), period_start=dt.timedelta(days=1, hours=8))


class WorkWednesday(mock_data_classes.DemandSource):
    def __init__(self):
        super().__init__(30, "week", dt.timedelta(hours=10), period_start=dt.timedelta(days=2, hours=8))


class WorkThursday(mock_data_classes.DemandSource):
    def __init__(self):
        super().__init__(30, "week", dt.timedelta(hours=10), period_start=dt.timedelta(days=3, hours=8))


class WorkFriday(mock_data_classes.DemandSource):
    def __init__(self):
        super().__init__(30, "week", dt.timedelta(hours=10), period_start=dt.timedelta(days=4, hours=8))


class DayTimeCommercial(mock_data_classes.DemandSource):
    def __init__(self):
        super().__init__(20, "day", dt.timedelta(hours=15), period_start=dt.timedelta(hours=7))


class EarlyMorningResidential(mock_data_classes.DemandSource):
    def __init__(self):
        super().__init__(20, "day", dt.timedelta(hours=7), period_start=dt.timedelta(hours=0))


class LateNightResidential(mock_data_classes.DemandSource):
    def __init__(self):
        super().__init__(20, "day", dt.timedelta(hours=2), period_start=dt.timedelta(hours=22))


# def display_data(recording_interval, recordings, start_point, occupancy_data):
#     start = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(start_point))
#     timeline = pd.date_range(start, freq=f'{recording_interval}min', periods=recordings)
#     #total_time = recording_interval * 60 * recordings
#     #timeline = np.linspace(start_point % total_time
#                            #, start_point % total_time + total_time, num=recordings)
#
#     df = pd.DataFrame({'Timestamp': timeline, 'Spaces Occupied': occupancy_data})
#     print(df)
#     chart = alt.Chart(df).mark_line().encode(
#         x='Timestamp',
#         y='Spaces Occupied'
#     ).interactive()
#     chart.show()


if __name__ == "__main__":
    recording_interval = 5
    recordings = 60 * 24 * 3
    start_point = time.time()
    display_data(recording_interval, recordings, start_point, fake_data(recording_interval, recordings, start_point))
