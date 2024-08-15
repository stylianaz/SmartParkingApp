import datetime
import functools
import pickle
from datetime import datetime as dt

import pandas as pd
import pytz
import requests

import data_gathering.API_keys as API_keys
import data_gathering.data_config as config
import data_gathering.data_gather_logging as logging
import ml_logger
import file_paths
import pickle_helpers as pickle_helpers

# this file tracks when live data was last received for each carpark so that repeat data is not gathered
LAST_UPDATE = "sydney_last_updates.bin"

# Data from https://opendata.transport.nsw.gov.au/dataset/car-park-api


def get_metadata_from_source():
    ml_logger.log(f"Gathering Sydney metadata")
    metadata = config.get_new_metadata_df()

    metadata["carpark_id"] = list(range(1, 6)) + [11, 13] + list(range(486, 490))
    metadata["name"] = ["Tallawong Station Car Park", "Kellyville Station Car Park", "Bella Vista Station Car Park",
                        "Hills Showground Station Car Park", "Cherrybrook Station Car Park",
                        "Narrabeen Car Park", "Dee Why Car Park", "Ashfield Car Park",
                        "Kogarah Car Park", "Seven Hills Car Park", "Manly Vale Car Park"]

    metadata["city"] = "Sydney"
    metadata["capacity"] = [1004, 1374, 800, 600, 400, 46, 117, 180, 259, 1613, 142]
    metadata["cost"] = -1
    metadata["latitude"] = [-33.69163, -33.713514, -33.730592, -33.72782, -33.736703, -33.713514, -33.752797,
                            -33.8875506079, -33.9621493059, -33.774430434, -33.786247]
    metadata["longitude"] = [150.906022, 150.935304, 150.944024, 150.987345, 151.031977,
                             151.297315, 151.286485, 151.125504163, 151.132641462, 150.936513359, 151.26671]
    metadata = config.enforce_metadata_types(metadata)
    return metadata


def get_facility_list():
    unfiltered = make_get_request()
    # carparks 6 and 7 are refreshed every 10 seconds! Including them slows things down a lot.
    unfiltered.pop('6')
    unfiltered.pop('7')
    return unfiltered


def check_for_carparks_that_need_attention(metadata, currently_seen):
    carparks = []
    for carpark_id in currently_seen:
        name = currently_seen[carpark_id]
        if metadata[metadata["carpark_id"] == int(carpark_id)].empty:
            carparks.append(int(carpark_id))
            logging.log("Sydney", f"Add new carpark with carpark_id {carpark_id} and name {name}", is_todo=True)
        elif metadata[metadata["carpark_id"] == int(carpark_id)].iloc[0]["name"] != name:
            carparks.append(int(carpark_id))
            logging.log("Sydney", f"Update name of carpark with carpark_id {carpark_id} to {name}", is_todo=True)
    return carparks


def make_get_request(params=None, historical=False):
    url = "https://api.transport.nsw.gov.au/v1/carpark"
    url += "/history" if historical else ""
    headers = {"Authorization": API_keys.SYDNEY}
    r = requests.get(url, headers=headers, params=params)
    return r.json()


def get_latest_data_for(carpark_id):
    return make_get_request(params={"facility": carpark_id})


def get_historical_data_for(carpark_id, date):
    return make_get_request(params={"facility": carpark_id, "eventdate": date}, historical=True)


def time_localised(time):
    return pd.to_datetime(time, format="%Y-%m-%dT%H:%M:%S").tz_localize("Australia/Sydney", ambiguous="NaT")


def get_timings_from_last():
    try:
        with open(file_paths.path_from_root("data", "trackers", LAST_UPDATE),
                  "rb") as pickle_jar:
            last_updated = pickle.load(pickle_jar)
        return last_updated
    except FileNotFoundError:
        return {}


class NewnessChecker(object):
    def __init__(self):
        self.last_updated = get_timings_from_last()

    def is_original(self, api_response):
        facility_id = api_response["facility_id"]
        if facility_id in self.last_updated and self.last_updated[facility_id] == api_response["MessageDate"]:
            return False
        self.last_updated[api_response["facility_id"]] = api_response["MessageDate"]
        return True

    def save(self):
        with open(file_paths.path_from_root("data", "trackers", LAST_UPDATE), "wb") as pickle_jar:
            pickle.dump(self.last_updated, pickle_jar)


def parse_single_api_response(api_response):
    spaces_occupied = api_response["occupancy"]["total"]
    capacity = api_response["spots"]
    localised_time = time_localised(api_response["MessageDate"])

    return {"carpark_id": api_response["facility_id"], "time": localised_time,
            "spaces_occupied": spaces_occupied, "capacity": capacity}


def get_main_data_for(facilities, carparks_to_ignore):
    main_data = config.get_new_main_data_df("Australia/Sydney")

    originality_checker = NewnessChecker()

    counter = 0
    for carpark_id in facilities:
        if int(carpark_id) in carparks_to_ignore:
            continue

        api_response = get_latest_data_for(carpark_id)
        if not originality_checker.is_original(api_response):
            continue
        try:
            to_add = parse_single_api_response(api_response)
        except pytz.AmbiguousTimeError:
            # losing 2 hrs of data a year is a small price for the cost of this headache
            continue
        main_data.loc[counter, :] = to_add
        counter += 1

    originality_checker.save()
    main_data = config.enforce_main_data_types(main_data, "Australia/Sydney")
    return main_data


def get_new_data_from_source(metadata):
    current_facilities = get_facility_list()
    problem_carparks = check_for_carparks_that_need_attention(metadata, current_facilities)
    ml_logger.log(f"Gathering latest Sydney data.")
    main_data = get_main_data_for(current_facilities, problem_carparks)
    ml_logger.log(f"Done gathering latest Sydney data.")
    return main_data


def get_historical_data_from_source(metadata):
    current_facilities = get_facility_list()
    check_for_carparks_that_need_attention(metadata, current_facilities)

    endpoint_in_sydney = dt.now(tz=datetime.timezone(datetime.timedelta(hours=10)))
    now_in_sydney = endpoint_in_sydney.strftime("%Y-%m-%d")

    days_of_required_data = config.days_of_historical_data
    start_in_sydney = (endpoint_in_sydney - datetime.timedelta(days=days_of_required_data-1)).strftime("%Y-%m-%d")

    date_range = pd.date_range(start=start_in_sydney, end=now_in_sydney, freq="1D")
    main_data = config.get_new_main_data_df("Australia/Sydney")

    counter = 0
    ml_logger.log("Gathering historical data for Sydney - takes a long time, but only has to be done once...")
    for carpark_id in current_facilities:
        ml_logger.log(f"Gathering for carpark {carpark_id}")
        for date in date_range:
            ml_logger.log(f"Gathering for date {date}")
            data = get_historical_data_for(carpark_id, date)
            for entry in data:
                try:
                    to_add = parse_single_api_response(entry)
                except pytz.AmbiguousTimeError:
                    # losing 2 hrs of data a year is a small price for the cost of this headache
                    continue
                main_data.loc[counter, :] = to_add
                counter += 1
    return main_data


def get_data(get="historical"):
    metadata = pickle_helpers.get_from_file_else("sydney_metadata.bin", get_metadata_from_source)
    if get == "metadata":
        return metadata
    elif get == "new":
        return get_new_data_from_source(metadata)
    else:
        return pickle_helpers.get_from_file_else("sydney_historical_data.bin",
                                                 functools.partial(get_historical_data_from_source, metadata))


if __name__ == '__main__':
    main_data = get_data(get="new")
    print(main_data)