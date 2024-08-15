import tensorflow as tf
import numpy as np
import pandas as pd
import datetime
import time
import os

import data_gathering.data_processing as data_gatherer
import data_gathering.data_config as data_config
import mySQLConnection
import scheduled_training.training as training
import scheduled_training.training_config as training_config
import file_paths
import ml_logger


# ml_interface is the root of the prediction service
# the functions it provides are gather_all_new_data, retrain_model and get_prediction


# this function ensures that both the database and the stored pickled files for training are updated with the latest data
def gather_all_new_data():
    ml_logger.log("getting data from data ml pipeline")
    new_metadata, non_appending_data, dynamic_data = data_gatherer.store_and_get_new_data()
    ml_logger.log("finished getting data from data ml pipeline")
    if new_metadata is not None:
        persist_new_carparks(new_metadata)
    if non_appending_data is not None:
        persist_occupancy_data(non_appending_data)
    if dynamic_data is not None:
        persist_occupancy_data(dynamic_data)


def retrain_model():
    ml_logger.log("retraining")
    training.train_individual_model()
    ml_logger.log("training complete")


# TODO: speed up batch predictions for fuzzy logic by creating a batch tensor
def get_prediction(external_id, target_timestamp):
    ml_logger.log(target_timestamp)

    # target_timestamp should always be a UTC timestamp
    try:
        model = tf.keras.models.load_model(file_paths.path_from_root("scheduled_training", "saved_models", "saved_model"))
    except OSError:
        # this means we are getting a prediction for the laravel site and so have a different root
        model = tf.keras.models.load_model(os.path.join("/smart_parking", "new_parking_prediction",
            "scheduled_training", "saved_models", "saved_model"))

    data = get_latest_occupancy_data(external_id)
    data, last_seen_time = prep_data(external_id, data)
    ml_logger.log(data)

    tensor = tf.convert_to_tensor(data)
    target_time_entries = training_config.get_input_width()
    tensor = tf.reshape(tensor, [1, target_time_entries, len(training_config.all_features)]) #  bug here

    predictions = model.predict(tensor)
    predictions = tf.reshape(predictions, [training_config.LABEL_WIDTH])

    # this entire section exists purely to place the target_timestamp relative to the times associated with each of the
    # model predictions and get the interpolated value give the surrounding predictions
    # this allows users to make arbitrary predictions instead of having to keep them in 30m intervals
    minutes_per_sample = data_config.TARGET_MINUTE_INTERVAL
    sample_time_delta = datetime.timedelta(minutes=minutes_per_sample)
    date_range = pd.date_range(start=last_seen_time + sample_time_delta,
                               end=last_seen_time + sample_time_delta * training_config.LABEL_WIDTH,
                               freq=f"{minutes_per_sample}min")
    ml_logger.log(last_seen_time)
    ml_logger.log(date_range)

    combined = pd.DataFrame({"time": date_range, "prediction": predictions})
    combined = combined.astype({"time": "datetime64", "prediction": "float64"})

    # try taking out the int cast ;J
    time_to_add = [pd.to_datetime(int(target_timestamp) * 10**9)]
    new_row = pd.DataFrame({"time": time_to_add, "prediction": [np.nan]})
    new_row = new_row.astype({"time": "datetime64", "prediction": "float64"})
    combined = combined.append(new_row)
    combined = combined.reset_index(drop=True)
    remembered_index = combined.index[-1]
    combined = combined.sort_values(by=["time"])

    combined = combined.reset_index()
    combined = combined.set_index("time")
    ml_logger.log(combined)

    interpolated = combined.interpolate(method='index')
    interpolated = interpolated.set_index("index")
    ml_logger.log(interpolated)

    final_prediction = interpolated.loc[remembered_index ,"prediction"]
    ml_logger.log(final_prediction)

    if final_prediction == np.nan:
        ml_logger.log("Could not make prediction")
        return None

    return int((1 - min(max(final_prediction, 0), 1)) * get_capacity(external_id))


def get_external_id(dataset_id, carpark_id):
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor(dictionary=True)
    query = ("SELECT main_id FROM ml_datasets "
             "WHERE dataset_id = %(dataset_id)s and dataset_internal_id = %(dataset_internal_id)s")
    cursor.execute(query, {"dataset_id": dataset_id, "dataset_internal_id": carpark_id})
    to_return = cursor.fetchone()["main_id"]
    cursor.close()
    return to_return


def get_internal_id(external_id):
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor(dictionary=True)
    query = ("SELECT dataset_id, carpark_id FROM ml_datasets "
             "WHERE main_id = %s")
    cursor.execute(query, external_id)
    to_return = cursor.fetchone()["dataset_id"], cursor.fetchone()["carpark_id"]
    cursor.close()
    return to_return


def get_total_carparks():
    query = "SELECT COUNT(*) AS total_carparks FROM places"
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute(query)
    total_carparks = cursor.fetchone()["total_carparks"]
    cursor.close()
    return total_carparks


def construct_places_row(metadata_row):
    # TODO: get more of this data to replace placeholders. See app/Geocoding/Geocoder.php - OpenDataApi for ideas
    ml_logger.log(f"new carpark: {metadata_row}")
    return ("NULL", metadata_row["name"], metadata_row["latitude"], metadata_row["longitude"], metadata_row["latitude"],
            metadata_row["longitude"], -1, -1, -1, -1, -1, 1003, -1, 7, -1, -1, metadata_row["capacity"], -1, -1, -1,
            "Added from ML datasets", 1, 1003, "NULL", "NULL")


def persist_new_carparks(new_metadata):
    ml_logger.log("persisting new metadata")

    insert_metadata = ("INSERT INTO places ("
                       "id, name, loc, lat, `long`, disabledcount, occupied, emptyspaces, empty, avaliable, "
                       "user_id, cost, parkingtype_id, reportedcount, validity, capacity, time, maximumduration, "
                       "source_id, comments, opendata, provider_id, created_at, updated_at"
                       ") VALUES "
                       "(%s, %s, GeomFromText('POINT(%s %s)'), %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)")
    get_count_in_ml_datasets_tracker = "SELECT COUNT(*) AS total_count FROM ml_datasets WHERE dataset_id = %s and dataset_internal_id = %s"
    insert_id_mapping = "INSERT INTO ml_datasets (main_id, dataset_id, dataset_internal_id, is_dynamic) VALUES (%s, %s, %s, %s)"
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor(dictionary=True)

    for index, metadata_row in new_metadata.iterrows():
        dataset_id = metadata_row["dataset_id"]
        carpark_id = metadata_row["carpark_id"]

        cursor.execute(get_count_in_ml_datasets_tracker, (dataset_id, carpark_id))
        if int(cursor.fetchone()["total_count"]) > 0:
            continue

        cursor.execute(insert_metadata, construct_places_row(metadata_row))
        conn.commit()
        new_id = get_total_carparks()

        is_dynamic = data_config.DATASET_SPECS[dataset_id]["is_dynamic"]
        cursor.execute(insert_id_mapping, (new_id, dataset_id, metadata_row["carpark_id"], is_dynamic))
        conn.commit()
    cursor.close()


def persist_occupancy_data(occupancy_data):
    ml_logger.log("persisting new occupancy data")

    conn = mySQLConnection.get_connection()
    cursor = conn.cursor()
    insert_recording = "INSERT INTO occupancy (carpark_id, spaces_available, timestamp) VALUES (%s, %s, %s)"

    occupancy_data = occupancy_data.sort_values(by=['dataset_id', 'carpark_id'])
    to_insert = []
    current_internal_id = (None,)
    external_id = None
    for index, row in occupancy_data.iterrows():
        if (row["dataset_id"], row["carpark_id"]) != current_internal_id:
            ml_logger.log(f"Switching to {row['dataset_id']}:{row['carpark_id']}")
            current_internal_id = (row["dataset_id"], row["carpark_id"])
            external_id = get_external_id(row["dataset_id"], row["carpark_id"])
        spaces_available = int(row["capacity"]) - int(row["spaces_occupied"])
        timestamp = row["time"].value / 10**9
        to_insert.append((external_id, spaces_available, timestamp)) # TODO: ensure these are UTC

    cursor.executemany(insert_recording, to_insert)
    conn.commit()
    cursor.close()


def get_capacity(external_id):
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor(dictionary=True)
    query = ("SELECT capacity FROM places "
             "WHERE id = %(external_id)s")
    cursor.execute(query, {"external_id": external_id})
    to_return = cursor.fetchone()["capacity"]
    cursor.close()
    return int(to_return)


def get_latest_occupancy_data(external_id):
    target_size = training_config.get_input_width()
    query = ("SELECT spaces_available, timestamp FROM occupancy "
             "WHERE carpark_id = %s and timestamp > %s "
             "ORDER BY timestamp ASC")
    conn = mySQLConnection.get_connection()
    cursor = conn.cursor()
    ml_logger.log(f"target input size: {target_size}")
    # 1.1 for some margin of potentially missing data
    cursor.execute(query, (external_id, time.time() - target_size * 30 * 60 * 1.1))

    spaces = []
    timestamps = []
    for (spaces_available, timestamp) in cursor:
        spaces.append(spaces_available)
        timestamps.append(timestamp)

    data = pd.DataFrame({"spaces_occupied": spaces, "timestamp": timestamps})
    data["spaces_occupied"] = get_capacity(external_id) - data["spaces_occupied"]
    return data


# make calls to various functions from data_processing to get the latest occupancy data for this carpark
# prepared as an input for the stored model
def prep_data(external_id, data):
    data["capacity"] = get_capacity(external_id)
    data["time"] = pd.to_datetime(data["timestamp"] * 10**9)
    ml_logger.log(data)

    data = data_gatherer.perform_time_interpolation(data, relaxed=True)
    data = data.reset_index()
    data = data.set_index("index")
    ml_logger.log(data)

    data = data.loc[data["time"] < pd.to_datetime(time.time() * 10**9)]

    last_row = data.iloc[-1]
    last_seen_time = last_row["time"]

    if not pd.notna(data["spaces_occupied"]).all():
        ml_logger.log("Could not make prediction because recent data is incomplete")
        return

    processed = data_gatherer.process_chunk(data)[0]
    return processed.iloc[-training_config.get_input_width():], last_seen_time
