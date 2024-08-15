import numpy as np
import tensorflow as tf
from matplotlib import pyplot as plt

import scheduled_training.training_with_data_api as training_with_data_api
import scheduled_training.training_config as config
import scheduled_training.data_windowing as data_windowing
import file_paths
import data_gathering.data_processing as data_processing
import data_gathering.root_data_gatherer as root_data_gatherer
import pandas as pd
import pickle_helpers as pickle_helpers


# generates a results DataFrame file at scheduled_training/results/results.bin containing the test_loss of each model
# with paramaterised label_length
def compare_all_models():
    metadata, main_data = data_processing.store_and_get_existing_training_data()
    timeseries_experiment = training_with_data_api.PredictionInterface(main_data, metadata)

    lookback_window = 48 * 7
    label_lengths = [10, 48 * 7]
    models = ["baseline", "linear", "CNN", "LSTM"]

    results = pd.DataFrame(columns=["model", "label_width", "training_time", "val_mae", "test_mae"])
    counter = 0
    for label_width in label_lengths:
        for model_name in models:
            model = config.Baseline(0, label_width) if model_name == "baseline" else config.names[model_name](label_width)

            input_width = config.get_input_width(lookback_window, config.CONV_WIDTH, model)
            window_generator = data_windowing.WindowGenerator(
                input_width=input_width,
                label_width=label_width, shift=label_width,
                label_columns=config.OUTPUT_FEATURES)

            time_taken = timeseries_experiment.train_on(lambda: model,
                                                        config.CARPARKS_TO_TRAIN_ON,
                                                        window_generator,
                                                        holdback_test_ratio=config.HOLDBACK)
            val_mae, test_mae = timeseries_experiment.test_on(additional_carparks=config.ADDITIONAL_TESTING_CARPARKS,
                                                              suppress_plotting=True)

            results.loc[counter, :] = [model_name, label_width, time_taken, val_mae, test_mae]
            counter += 1

    pickle_helpers.save_data_to_pickle_jar(results, file_paths.path_from_root("scheduled_training", "results", "results.bin"))


# this is used by the ml_interface - hence training_config contains the configuration of the model that gets used
# by the laravel site
def train_individual_model(model=config.MODEL, base_width=config.BASE_WIDTH, label_width=config.LABEL_WIDTH):
    metadata, main_data = data_processing.store_and_get_existing_training_data()

    input_width = config.get_input_width(base_width, config.CONV_WIDTH, model)

    timeseries_experiment = training_with_data_api.PredictionInterface(main_data, metadata)
    time_taken = timeseries_experiment.train_on(lambda: model,
                                                config.CARPARKS_TO_TRAIN_ON,
                                                data_windowing.WindowGenerator(
                                                    input_width=input_width,
                                                    label_width=label_width, shift=label_width,
                                                    label_columns=config.OUTPUT_FEATURES),
                                                holdback_test_ratio=config.HOLDBACK)
    val_mae, test_mae = timeseries_experiment.test_on(additional_carparks=config.ADDITIONAL_TESTING_CARPARKS, suppress_plotting=True)
    timeseries_experiment.model.save(file_paths.path_from_root("scheduled_training", "saved_models", "saved_model"))
    return val_mae, test_mae, time_taken


# this is purely for some data exploration - not used by the main site
def data_probing():
    all_metadata, all_main_data, dynamic_data_to_append = root_data_gatherer.get_data(False)
    metadata, main_data = all_metadata, all_main_data

    print(main_data.describe())
    print(f"No cars detected in {len(main_data[main_data['spaces_occupied'] == 0])} rows.")

    print(main_data[main_data["carpark_id"] == 0])
    plotting(main_data[main_data["carpark_id"] == 0])

    multi_indexed = main_data.set_index(["dataset_id", "carpark_id"])
    multi_meta = metadata.set_index(["dataset_id", "carpark_id"])
    joined = multi_indexed.join(multi_meta)
    joined["spaces_occupied"] = joined["spaces_occupied"].div(joined["capacity"])

    print(f"Rows above capacity:\n{joined.loc[joined['spaces_occupied'] > 1.0, ['spaces_occupied']]}")
    print(f"After dividing occupancy by capacity:\n{joined.describe()}")

    joined.loc[joined["spaces_occupied"] > 1.0, ["spaces_occupied"]] = 1.0

    print(f"After removing >1.0 occupancies:\n{joined.describe()}")

    print(f"Final result:\n{joined[['time', 'spaces_occupied']]}")

    fourier_transform(joined)

    print(joined["time"])

    timestamps = joined["time"].map(pd.Timestamp.timestamp)
    day_length = 24*60*60
    year_length = day_length * 365.2425
    joined["day_sin"] = np.sin(2 * np.pi * timestamps / day_length)
    joined["day_cos"] = np.cos(2 * np.pi * timestamps / day_length)
    joined["year_sin"] = np.sin(2 * np.pi * timestamps / year_length)
    joined["year_cos"] = np.cos(2 * np.pi * timestamps / year_length)

    print(joined)

    count_discontinuities(joined)


def fourier_transform(joined):
    # converting occupancy data to frequency signals
    fft = tf.signal.rfft(joined['spaces_occupied'])

    # scaling a plot so it shows the frequencies logarithmically along the x axis
    frequencies = np.arange(0, len(fft))
    n_samples = len(joined['spaces_occupied'])
    samples_per_year = 2 * 24 * 365.2425
    years_covered = n_samples / samples_per_year
    f_per_year = frequencies / years_covered
    plt.step(f_per_year, np.abs(fft))
    plt.xscale('log')

    # rescaling to suitable y by trial and error
    plt.ylim(0, 8000)
    plt.xlim([0.1, max(plt.xlim())])
    plt.xticks([1, 365.2425], labels=['1/Year', '1/day'])
    _ = plt.xlabel('Frequency (log scale)')
    plt.show()


# just to analyse the frequency of data breaks in the raw data
def count_discontinuities(df):
    df = df.reset_index()
    pivoted = df.pivot(index="time", columns=["dataset_id", "carpark_id"],
                       values=training_with_data_api.ALL_COLUMNS)
    time_series = pd.Series(pivoted.index)
    print(time_series)

    gaps = []
    time_iter = iter(time_series)
    last = next(time_iter)
    for current in time_iter:
        gaps.append(current - last)
        last = current

    print(list(filter(lambda x: x.seconds > 3600, gaps)))


def plotting(example):
    plt.scatter(example["time"], example["spaces_occupied"],
                edgecolors='k', label='Labels', c='#2ca02c', s=64)
    plt.show()


if __name__ == "__main__":
    compare_all_models()
