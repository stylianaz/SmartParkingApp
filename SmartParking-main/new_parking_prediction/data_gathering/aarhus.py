import csv
import pandas as pd
import numpy as np
import functools

import file_paths
import pickle_helpers as pickle_helpers
import data_gathering.data_config as config
import ml_logger

# Data from http://iot.ee.surrey.ac.uk:8080/datasets.html


def get_metadata_from_source():
    with open(file_paths.path_from_root("data", "original_data_sources", "aarhus_parking_address.csv"),
              newline='') as csv_file:
        reader = csv.reader(csv_file, delimiter=',', quotechar='|')

        counter = 0
        metadata = config.get_new_metadata_df()
        next(reader)
        for row in reader:
            metadata.loc[counter, :] = {"carpark_id": counter,
                                        "name": row[0],
                                        "city": row[1],
                                        "cost": -1,
                                        "latitude": row[-2],
                                        "longitude": row[-1]}
            counter += 1

    metadata["capacity"] = [65, 130, 953, 512, 1240, 700, 400, 210]
    metadata = config.enforce_metadata_types(metadata)
    return metadata


def fix_ambiguous_dst_switches(main_data):
    main_data.loc[pd.isnull(main_data["time"]), ["time"]] = pd.to_datetime("26/10/2014 00:28", format="%d/%m/%Y %H:%M")\
        .tz_localize("UTC", ambiguous=np.array(False)).tz_convert("CET")
    return main_data


def convert_aarhus_to_standard(df, metadata):
    df.drop(["streamtime", "_id"], axis=1, inplace=True)
    df = df.astype({'vehiclecount': 'int64'})
    names = metadata.loc[:, "name"]
    id_mapping = {names[i]: i for i in range(len(names))}
    df.loc[:, "garagecode"] = [id_mapping[name] for name in df.loc[:, "garagecode"]]
    df["updatetime"] = pd.to_datetime(df["updatetime"], format="%d/%m/%Y %H:%M").dt.tz_localize("CET", ambiguous="NaT")
    df.rename(columns={"garagecode": "carpark_id",
                       "vehiclecount": "spaces_occupied",
                       "updatetime": "time",
                       "totalspaces": "capacity"}, inplace=True)
    df = fix_ambiguous_dst_switches(df)
    duplicates = df.drop("spaces_occupied", axis=1).duplicated()
    df = df[~duplicates]
    return df[["carpark_id", "time", "spaces_occupied", "capacity"]]


def convert_to_df(csv_file_name):
    ml_logger.log("Gathering historical data for Aarhus - takes a long time, but only has to be done once...")

    with open(file_paths.path_from_root("data", "original_data_sources", csv_file_name), newline='') as csv_file:
        reader = csv.reader(csv_file, delimiter=',', quotechar='|')

        reader_iterator = iter(reader)

        df = pd.DataFrame(columns=next(reader_iterator))

        counter = 0
        for row in reader:
            # if counter > 100:
               # break
            df.loc[counter, :] = row
            counter += 1
    return df


def get_main_data_from_source(metadata):
    main_data = convert_to_df('aarhus_parking_2014.csv')
    main_data = convert_aarhus_to_standard(main_data, metadata)
    return main_data


def get_data(get="historical"):
    metadata = pickle_helpers.get_from_file_else("aarhus_metadata.bin", get_metadata_from_source)
    if get == "metadata":
        return metadata
    else:
        return pickle_helpers.get_from_file_else("aarhus_main_data.bin", functools.partial(get_main_data_from_source, metadata))


if __name__ == "__main__":
    data = get_data()
    print(data)
