import pandas as pd
import numpy as np

import data_gathering.data_config as config
import data_gathering.root_data_gatherer as root_data_gatherer
import pickle_helpers as pickle_helpers


# this script is where the heavy lifting to turn raw occupancy data into clean streams of evenly spaced, contiguous
# temporal data occurs

METADATA_FILENAME = "all_metadata.bin"
TEMPORAL_DATA_FILENAME = "all_temporal_data.bin"


# see the fourier transform in scheduled_training/training.py for justification of these engineered features
def transform_time_feature(stream_frame):
    timestamps = stream_frame["time"].map(pd.Timestamp.timestamp)
    day_length = 24 * 60 * 60
    week_length = day_length * 7
    stream_frame["day_sin"] = np.sin(2 * np.pi * timestamps / day_length)
    stream_frame["day_cos"] = np.cos(2 * np.pi * timestamps / day_length)
    stream_frame["week_sin"] = np.sin(2 * np.pi * timestamps / week_length)
    stream_frame["week_cos"] = np.cos(2 * np.pi * timestamps / week_length)

    return stream_frame[config.ALL_COLUMNS]


def normalise_occupancy(stream_frame):
    # divides into capacity - removes excess of 1 - drops capacity
    stream_frame["spaces_occupied"] = stream_frame["spaces_occupied"].div(stream_frame["capacity"])
    stream_frame.loc[stream_frame["spaces_occupied"] > 1.0, ["spaces_occupied"]] = 1.0
    stream_frame = stream_frame.astype({"spaces_occupied": "float64"})
    return stream_frame[["time", "spaces_occupied"]]


def tidy_stream_frame(stream_frame):
    stream_frame = stream_frame.reset_index(drop=True)
    stream_frame["spaces_occupied"] = stream_frame["spaces_occupied"].round(0).astype("int64")
    stream_frame["capacity"] = stream_frame["capacity"].round(0).astype("int64")
    return stream_frame


# used to determine where an interpolated set of data has non-continuities
def group_determiner():
    current_group_name = None
    last_seen = None

    def inner(index):
        nonlocal current_group_name, last_seen
        if last_seen is None or last_seen < index - 1:
            current_group_name = index

        last_seen = index
        return current_group_name

    return inner


def process_chunk(frame):
    frame = tidy_stream_frame(frame)
    frame = normalise_occupancy(frame)
    first = frame.iloc[0]
    start_time = first["time"]
    last = frame.iloc[len(frame) - 1]
    end_time = last["time"]
    frame = transform_time_feature(frame)
    frame = frame if len(frame) > 1 else None
    return frame, start_time, end_time


# places a set of desired times into the existing data, interpolates to fill these times and retrieves them by
# remembering their original indexes
def perform_time_interpolation(data, relaxed=False):
    last_row = data.iloc[-1]
    time_spine = pd.date_range(start=data.loc[0, "time"], end=last_row["time"], freq=config.target_sample_interval)
    added_frame = pd.DataFrame(columns=["time", "spaces_occupied", "capacity"])
    added_frame["time"] = time_spine
    old_length = len(data)
    altogether = pd.concat([data, added_frame])
    altogether = altogether.reset_index(drop=True)
    altogether = altogether.sort_values("time")
    altogether = altogether.astype({"time": "datetime64", "spaces_occupied": "float64", "capacity": "float64"})
    altogether = altogether.reset_index()
    altogether = altogether.set_index("time")

    if relaxed:
        interpolated = altogether.interpolate(method='index', limit=2)
    else:
        interpolated = altogether.interpolate(method='index', limit=2, limit_area='inside')

    spine_only = interpolated.loc[interpolated['index'] >= old_length]

    return spine_only


class DataCleaner(object):
    def __init__(self, temporal_data):
        self.temporal_data = temporal_data
        self.last_chunk_datum = self._get_initial_state()
        self.start_of_last = None
        self.end_of_last = None
        self.current_carpark = None
        self.current_dataset = None

    def _get_initial_state(self):
        try:
            return pickle_helpers.get_data_from_pickle_jar("data_for_last_chunks.bin", is_tracker=True)
        except FileNotFoundError:
            return {}

    def save(self):
        pickle_helpers.save_data_to_pickle_jar(self.last_chunk_datum, "data_for_last_chunks.bin", is_tracker=True)

    def split_into_acceptable_chunks(self, data):
        data = data.loc[pd.notna(data["spaces_occupied"])]
        data = data.reset_index()
        data = data.set_index("index")
        grouped = data.groupby(by=group_determiner(), axis="rows")

        chunk_dic = {}
        for group in grouped:
            processed, start_time, end_time = process_chunk(group[1])
            self.start_of_last = start_time
            self.end_of_last = end_time
            if processed is not None:
                chunk_dic[self.start_of_last] = processed

        return chunk_dic

    # see lower down for why raw data needs to be set aside
    def infer_last_chunk(self):
        relevant_section = self.temporal_data[self.current_dataset][self.current_carpark]
        time_mask = self.start_of_last < relevant_section["time"].astype("datetime64")
        time_mask = time_mask.shift(-1, fill_value=True)
        return relevant_section.loc[time_mask]

    def add_to_last_chunk_datum(self):
        if self.current_dataset not in self.last_chunk_datum:
            self.last_chunk_datum[self.current_dataset] = {}
        self.last_chunk_datum[self.current_dataset][self.current_carpark] = self.infer_last_chunk()

    def process_raw_data(self, data):
        spine_only = perform_time_interpolation(data)
        split_into_chunks = self.split_into_acceptable_chunks(spine_only)
        return split_into_chunks if split_into_chunks != {} else None

    def get_clean_data(self):
        result = {}
        for ds_key in self.temporal_data:
            result[ds_key] = {}
            self.current_dataset = ds_key
            for carpark_key in self.temporal_data[ds_key]:
                self.current_carpark = carpark_key
                if (chunked := self.process_raw_data(self.temporal_data[ds_key][carpark_key])) is not None:
                    result[ds_key][carpark_key] = chunked
                    self.add_to_last_chunk_datum()
        self.save()
        return result


def split_by_carpark(data):
    data = data.set_index(["dataset_id", "carpark_id"])

    split = {}
    level_O = set(data.index.get_level_values(0))
    for dataset_id in level_O:
        split[int(dataset_id)] = {}
        level_1 = set(data.loc[(dataset_id,), :].index.get_level_values(0))
        for carpark_id in level_1:
            carpark_data = data.loc[(dataset_id, carpark_id), :]

            if type(carpark_data) is pd.Series:
                new_data = pd.DataFrame(columns=["time", "spaces_occupied", "capacity"])
                new_data.loc[0, :] = {"time": carpark_data["time"],
                                      "spaces_occupied": carpark_data["spaces_occupied"],
                                      "capacity": carpark_data["capacity"]}
            else:
                new_data = carpark_data.reset_index()

            split[int(dataset_id)][int(carpark_id)] = new_data[["time", "spaces_occupied", "capacity"]]

    return split


def prep_for_cleaning(data):
    data = data.sort_values(by=['dataset_id', 'carpark_id', 'time'])
    return split_by_carpark(data)


def store_and_get_all_metadata(to_add):
    if to_add is None:
        return pickle_helpers.get_data_from_pickle_jar(METADATA_FILENAME)

    try:
        old_data = pickle_helpers.get_data_from_pickle_jar(METADATA_FILENAME)
        concatenated = pd.concat([old_data, to_add])
        pickle_helpers.save_data_to_pickle_jar(concatenated, METADATA_FILENAME)
        return concatenated
    except FileNotFoundError:
        pickle_helpers.save_data_to_pickle_jar(to_add, METADATA_FILENAME)
        return to_add

# the rest of the script manages the merging of new live data onto existing data stored in the pickle file
# "all_temporal_data.bin" - various pieces of state are tracked to make sure this merging occurs correctly
# deleting these files (found in processed_data) without deleting "all_temporal_data.bin" and "all_metadata.bin"
# will cause data corruption. However, this can be fixed by just going back to the data/original_sources - i.e. deleting
# all files in data/processed_data

class TemporalDataManager(object):
    def __init__(self):
        self.data = self._get_initial_state()

    def _get_initial_state(self):
        try:
            return pickle_helpers.get_data_from_pickle_jar(TEMPORAL_DATA_FILENAME)
        except FileNotFoundError:
            return {}

    def save(self):
        pickle_helpers.save_data_to_pickle_jar(self.data, TEMPORAL_DATA_FILENAME)

    def _enter_data(self, to_add):
        for ds_id, for_dataset in to_add.items():
            if ds_id not in self.data.keys():
                self.data[ds_id] = for_dataset
                continue

            for carpark_id, for_carpark in for_dataset.items():
                if carpark_id not in self.data[ds_id].keys():
                    self.data[ds_id][carpark_id] = for_carpark
                    continue

                self.data[ds_id][carpark_id].update(for_carpark)

    def simple_addition(self, to_add):
        prepped = prep_for_cleaning(to_add)
        cleaner = DataCleaner(prepped)
        self._enter_data(cleaner.get_clean_data())

    def _peel_last_stream_of(self, ds_id, carpark_id):
        return list(self.data[ds_id][carpark_id].items())[-1][1]

    def _merge_new_data_onto_stream(self, ds_id, carpark_id, to_add):
        # attaching onto original raw data, not the interpolated data in "all_temporal_data.bin"
        last_chunks = pickle_helpers.get_data_from_pickle_jar("data_for_last_chunks.bin", is_tracker=True)
        last_time = last_chunks[ds_id][carpark_id]
        merged = pd.concat([last_time, to_add])
        merged["dataset_id"] = ds_id
        merged["carpark_id"] = carpark_id
        prepped = prep_for_cleaning(merged[["dataset_id"] + config.MAIN_DATA_COLUMNS])
        cleaner = DataCleaner(prepped)
        cleaned = cleaner.get_clean_data()
        return cleaned


    # all this actually does is take the last chunk of data from each carpark and attach the latest live data
    # this attachment requires restoring the raw data from "data_for_last_chunks.bin" since the data processing
    # done to the saved chunks is not reversible.
    def complex_addition(self, to_add):
        split = prep_for_cleaning(to_add)
        adding_simply = {}
        for ds_id, for_dataset in split.items():
            if ds_id not in self.data.keys():
                adding_simply[ds_id] = for_dataset
                continue

            for carpark_id, for_carpark in for_dataset.items():
                if carpark_id not in self.data[ds_id].keys():
                    if ds_id not in adding_simply:
                        adding_simply[ds_id] = {}
                    adding_simply[ds_id][carpark_id] = for_carpark
                    continue

                # take last chunk
                self._peel_last_stream_of(ds_id, carpark_id)
                merged = self._merge_new_data_onto_stream(ds_id, carpark_id, for_carpark)
                self._enter_data(merged)

        cleaner = DataCleaner(adding_simply)
        self._enter_data(cleaner.get_clean_data())


def process_and_store_data(non_appending_data, dynamic_data):
    data_manager = TemporalDataManager()

    if non_appending_data is not None:
        data_manager.simple_addition(non_appending_data)
    if dynamic_data is not None:
        data_manager.complex_addition(dynamic_data)

    data_manager.save()

    return data_manager


# updates live sources
def store_and_get_new_data():
    new_metadata, non_appending_data, dynamic_data = root_data_gatherer.get_data(True)
    process_and_store_data(non_appending_data, dynamic_data)
    store_and_get_all_metadata(new_metadata)
    return new_metadata, non_appending_data, dynamic_data

# ignores live sources but will still grab data from CSVs/historical sets if not already done so
def store_and_get_existing_training_data():
    new_metadata, non_appending_data, dynamic_data = root_data_gatherer.get_data(False)
    data_manager = process_and_store_data(non_appending_data, dynamic_data)
    return store_and_get_all_metadata(new_metadata), data_manager.data


if __name__ == '__main__':
    store_and_get_new_data()
