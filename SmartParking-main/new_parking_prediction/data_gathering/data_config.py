import data_gathering.aarhus as aarhus
import data_gathering.sydney as sydney
import pandas as pd
import scheduled_training.training_config as training_config


# this is extremely important - all specifications relating to which data is gathered and expected should be here
# the only other file where parameters are gathered is scheduled_training.training_config


# to add a new dataset, it will need to be registered statically here
DATASET_SPECS = {0: {"gatherer": aarhus.get_data,
                     "is_dynamic": False,},
                 1: {"gatherer": sydney.get_data,
                     "is_dynamic": True}}

TARGET_MINUTE_INTERVAL = 30
target_sample_interval = f"{TARGET_MINUTE_INTERVAL}min"
days_of_historical_data = int(training_config.get_input_width() * TARGET_MINUTE_INTERVAL / 60 / 24)

FEATURE_COLUMNS = training_config.ADDED_FEATURE_COLUMNS
ALL_COLUMNS = training_config.OUTPUT_FEATURES + FEATURE_COLUMNS

METADATA_COLUMNS = ["carpark_id", "name", "city", "capacity", "cost", "latitude", "longitude"]
MAIN_DATA_COLUMNS = ["carpark_id", "time", "spaces_occupied", "capacity"]


# the following functions are used to get the correct format of DataFrame for raw occupancy data and carpark metadata
def get_new_metadata_df():
    metadata = pd.DataFrame(columns=METADATA_COLUMNS)
    metadata = enforce_metadata_types(metadata)
    return metadata


def enforce_metadata_types(data_frame):
    return data_frame.astype({'carpark_id': 'int64', 'name': 'str', 'city': 'str', 'cost': 'int64',
                                'latitude': 'float64', 'longitude': 'float64'})


def get_new_main_data_df(timezone=None):
    frame = pd.DataFrame(columns=MAIN_DATA_COLUMNS)
    frame = enforce_main_data_types(frame, timezone)
    return frame


def enforce_main_data_types(data_frame, timezone=None):
    time_zone_info = f"[ns, {timezone}]" if timezone else ''
    return data_frame.astype({'carpark_id': 'int64', "time": "datetime64" + time_zone_info,
                              'spaces_occupied': 'int64', 'capacity': 'int64'})
