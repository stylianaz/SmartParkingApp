import file_paths

# so far just used to record when new carparks may have been added to live data streams - these may need manual attention

log_file = file_paths.path_from_root("data_gathering", "data_gathering_log.txt")


def log(dataset, message, is_todo=False):
    with open(log_file, "a") as file_handler:
        file_handler.write(log_message(dataset, message, is_todo))


def log_message(dataset, message, is_todo):
    return f"#DataGathering Dataset: {dataset} " + ("TODO: " if is_todo else "") + message + "\n"
