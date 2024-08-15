import pickle
import file_paths


def get_data_from_pickle_jar(file_name, is_tracker=False):
    directory = "trackers" if is_tracker else "processed_data"
    with open(file_paths.path_from_root("data", directory, file_name), "rb") as pickle_jar:
        return pickle.load(pickle_jar)


def save_data_to_pickle_jar(data, file_name, is_tracker=False):
    directory = "trackers" if is_tracker else "processed_data"
    with open(file_paths.path_from_root("data", directory, file_name), "wb") as pickle_jar:
        pickle.dump(data, pickle_jar)


def get_from_file_else(file_name, else_function, is_tracker=False):
    try:
        return get_data_from_pickle_jar(file_name, is_tracker)
    except FileNotFoundError:
        data = else_function()
        save_data_to_pickle_jar(data, file_name, is_tracker)
        return data
