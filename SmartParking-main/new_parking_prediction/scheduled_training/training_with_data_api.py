from typing import Callable

import scheduled_training.data_windowing as data_windowing
import scheduled_training.training_config as config
import tensorflow as tf
import numpy as np
import pandas as pd
import time
from matplotlib import pyplot as plt


# an object which allows repeat testing of models without having to access the data-windower or directly compile the models
class PredictionInterface(object):
    def __init__(self, temporal_data, carpark_data):
        self._temporal_data = temporal_data
        self._carpark_data = carpark_data

        self._validation_ratio = None
        self._testing_ratio = None

        self._next_training_set = None
        self._next_testing_set = None

        self.model = None
        self._window = None

    def train_on(self,
                 model_getter: Callable[[], tf.keras.Model],
                 carpark_indexes: list[tuple[int, int]],
                 window_generator: data_windowing.WindowGenerator,
                 validation_ratio: float = 0.2,
                 holdback_test_ratio: float = 0) \
            -> tf.keras.Model:
        self.model = model_getter()
        self._window = window_generator
        self._validation_ratio = validation_ratio
        self._testing_ratio = holdback_test_ratio

        training_data = self.select_data(carpark_indexes)

        self._window.submit_data(training_data)
        self._window.split_data(validation_ratio, holdback_test_ratio)

        start = time.time()
        self.compile_and_fit()
        return time.time() - start

    def compile_and_fit(self, patience=2):
        early_stopping = tf.keras.callbacks.EarlyStopping(monitor='val_loss',
                                                          patience=patience,
                                                          mode='min')

        self.model.compile(loss=tf.losses.MeanSquaredError(),
                           optimizer=tf.optimizers.Adam(),
                           metrics=[tf.metrics.MeanAbsoluteError()])

        history = self.model.fit(self._window.training_dataset, epochs=config.MAX_EPOCHS,
                                 validation_data=self._window.validation_dataset,
                                 callbacks=[early_stopping])
        return history

    def test_on(self, additional_carparks: list[tuple[int, int]] = None, suppress_plotting: bool = False) -> float:
        if additional_carparks is not None:
            carpark_to_add = additional_carparks[0] #  TODO: fix this
            new_data = self._temporal_data[carpark_to_add[0]][carpark_to_add[1]]
            self._window.add_more_test_data(new_data)

        val_results = self.model.evaluate(self._window.validation_dataset)
        print(f'\nValidation results - Loss: {val_results[0]} - MAE: {val_results[1]}')

        if not self._testing_ratio > 0 and additional_carparks is None:
            return val_results[1], None

        test_results = self.model.evaluate(self._window.testing_dataset)
        print(f'\nTest results - Loss: {test_results[0]} - MAE: {test_results[1]}')

        if not suppress_plotting:
            self._window.plot('spaces_occupied', model=self.model, max_subplots=5)
        plt.show()

        return val_results[1], test_results[1]

    # get all data for the chosen carpark_indexes
    def select_data(self, carpark_indexes):
        return [self._temporal_data[dataset_key][carpark_key][time_key]
                for dataset_key, dataset_data in self._temporal_data.items()
                for carpark_key, carpark_data in dataset_data.items()
                for time_key in carpark_data
                if (dataset_key, carpark_key) in carpark_indexes]

    def select_and_pivot_data(self, carpark_indexes):
        selected = self._temporal_data.loc[carpark_indexes]
        index_reset = selected.reset_index()
        pivoted = index_reset.pivot(index="time", columns=["dataset_id", "carpark_id"],
                                    values=config.all_features)
        pivoted = pivoted.reorder_levels(["dataset_id", "carpark_id", 0], axis="columns")
        return pivoted
