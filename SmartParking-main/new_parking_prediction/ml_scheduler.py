import threading
import schedule
import time
import mysql

import mySQLConnection
import data_generation.mock_data_generation as mock_data_generation
import data_gathering.data_config as data_config
import data_gathering.data_processing as data_gatherer
import ml_interface
import ml_logger
import os
import pickle_helpers
import file_paths

seconds_between_recordings = data_config.TARGET_MINUTE_INTERVAL * 60
# tracks when mock data was last generated
LAST_GENERATED = "last_generated.bin"


# ml_scheduler handles the scheduling of routine ml service tasks like generating mock data and model training
# see bottom of file for schedule


def processes_decorator(**kwargs):
    seconds_between_recordings = kwargs["seconds_between_recordings"]

    def inner(func):
        while True:
            start = time.time()
            func()
            time_taken = time.time() - start
            time.sleep(max(seconds_between_recordings - time_taken, 0.1))
    return inner


def run_on_separate_thread(func):
    thread = threading.Thread(target=func)
    thread.start()


# ensure we aren't generating into the future by repeatedly re-running the prediction service
def generate_if_current(mock_data_generator):
    last_generated = pickle_helpers.get_from_file_else(file_paths.path_from_root("data", "trackers", LAST_GENERATED),
                                                       lambda: 0, is_tracker=True)
    if time.time() >= last_generated + 60 * data_config.TARGET_MINUTE_INTERVAL:
        mock_data_generation.generate_mock_data(mock_data_generator)
        pickle_helpers.save_data_to_pickle_jar(time.time(), LAST_GENERATED,is_tracker=True)


def clear_carpark_data_tables():
    query = "TRUNCATE TABLE occupancy"
    query2 = "TRUNCATE TABLE ml_datasets"
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor()
    ml_logger.log("clearing database tables")
    try:
        cursor.execute(query)
        cursor.execute(query2)
    except mysql.connector.ProgrammingError:
        # just means the tables don't exist yet
        pass

    conn.commit()
    cursor.close()
    ml_logger.log("database tables cleared")


def clear_all_regeneratable_carpark_data():
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT main_id FROM ml_datasets")
    external_ids = [row["main_id"] for row in cursor]
    cursor.close()
    conn.close()

    clear_carpark_data_tables()

    conn = mySQLConnection.get_connection()
    cursor = conn.cursor()
    for id in external_ids:
        cursor.execute("DELETE FROM places where id = %s", (id,))
    conn.commit()
    cursor.close()


def remove_old_state():
    ml_logger.log("clearing old state")
    to_remove = os.listdir(file_paths.path_from_root("data", "trackers"))
    for file_name in to_remove:
        os.remove(file_paths.path_from_root("data", "trackers", file_name))

    try:
        os.remove(file_paths.path_from_root("data", "processed_data", data_gatherer.TEMPORAL_DATA_FILENAME))
    except FileNotFoundError:
        pass

    try:
        os.remove(file_paths.path_from_root("data", "processed_data", data_gatherer.METADATA_FILENAME))
    except FileNotFoundError:
        pass

    clear_all_regeneratable_carpark_data()


# one off when first run
remove_old_state()

ml_interface.gather_all_new_data()
mock_data_generator = pickle_helpers.get_from_file_else(file_paths.path_from_root("data", "trackers", "data_generation_manager.bin"),
    lambda: mock_data_generation.MockDataManager(seconds_between_recordings), is_tracker=True)
generate_if_current(mock_data_generator)
generate_if_current(mock_data_generator)
generate_if_current(mock_data_generator)
generate_if_current(mock_data_generator)

ml_logger.log(ml_interface.get_prediction(1, time.time() + 70 * 60))

# ml_interface.retrain_model()

# routinely occurring
schedule.every(10).minutes.do(run_on_separate_thread, ml_interface.gather_all_new_data)
# may want to think about getting new carparks to appear quicker!
schedule.every(30).minutes.do(run_on_separate_thread, lambda: generate_if_current(mock_data_generator))
schedule.every().day.at("02:00").do(run_on_separate_thread, ml_interface.retrain_model)

while True:
    schedule.run_pending()
    time.sleep(1)
