import file_paths
import utilities
import os

log_file = file_paths.path_from_root("ml_log.txt")


def log(message):
    with open(log_file, "a") as file_handler:
        file_handler.write(str(message))
        file_handler.write("\n")
        if os.path.abspath(__file__) == "/ml_pipeline/ml_logger.py":
            utilities.flush_print(message)

