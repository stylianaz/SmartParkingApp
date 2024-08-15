import data_gathering.data_config as config
import file_paths
import pickle
import pandas as pd

# to remember which datasets have already been seen before and hence can be skipped
DATASET_STATUS_TRACKER = "dataset_status_tracking.bin"


def get_dataset_status_tracker():
    try:
        with open(file_paths.path_from_root("data", "trackers", DATASET_STATUS_TRACKER), "rb") as pickle_jar:
            last_updated = pickle.load(pickle_jar)
        return last_updated
    except FileNotFoundError:
        return []


class NewnessChecker(object):
    def __init__(self):
        self._seen = get_dataset_status_tracker()

    def already_seen(self, dataset_id):
        return dataset_id in self._seen

    def mark_seen(self, dataset_id):
        self._seen.append(dataset_id)

    def save(self):
        with open(file_paths.path_from_root("data", "trackers", DATASET_STATUS_TRACKER), "wb") as pickle_jar:
            pickle.dump(self._seen, pickle_jar)


# processes each item of the datasets specification and gets the relevant data from either persisted binaries
# or the original sources. It then groups the data into datframes for carpark metadata, live and static
# occupancy data.
def get_data(should_get_new):
    all_metadata_frames = []
    non_appending_main_data_frames = []
    dynamic_data_to_append = []

    newness_checker = NewnessChecker()
    specs = config.DATASET_SPECS
    for dataset_id in specs:
        seen_before = True
        if not newness_checker.already_seen(dataset_id=dataset_id):
            seen_before = False
            newness_checker.mark_seen(dataset_id)
            metadata = specs[dataset_id]["gatherer"](get="metadata")
            metadata["dataset_id"] = dataset_id
            all_metadata_frames.append(metadata[["dataset_id"] + config.METADATA_COLUMNS])

        if specs[dataset_id]["is_dynamic"] and seen_before and should_get_new:
            main_data = specs[dataset_id]["gatherer"](get="new")
            main_data["dataset_id"] = dataset_id
            dynamic_data_to_append.append(main_data[["dataset_id"] + config.MAIN_DATA_COLUMNS])
        elif not seen_before:
            main_data = specs[dataset_id]["gatherer"]()
            main_data["dataset_id"] = dataset_id
            non_appending_main_data_frames.append(main_data[["dataset_id"] + config.MAIN_DATA_COLUMNS])

    newness_checker.save()

    all_metadata = pd.concat(all_metadata_frames) if all_metadata_frames else None
    all_main_data = pd.concat(non_appending_main_data_frames) if all_metadata_frames else None
    dynamic_data_to_append = pd.concat(dynamic_data_to_append) if dynamic_data_to_append else None
    return all_metadata, all_main_data, dynamic_data_to_append


if __name__ == "__main__":
    metadata, non_appending, dynamic = get_data(True)
    print(metadata)
