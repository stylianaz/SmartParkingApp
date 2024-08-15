import tensorflow as tf
import numpy as np
import matplotlib.pyplot as plt
import scheduled_training.training_config as config

# this object converts streams of timeseries occupancy data as produced by the data_gathering package, into training data
# ready for any of the models defined in scheduled_training.training_config
# this mainly involves windowing the timeseries by sliding across each stream, creating an example at each appropriate
# location and adding it into a tensorflow dataset.
class WindowGenerator(object):
    def __init__(self, input_width, label_width, shift,
                 label_columns=None):

        self._main_data = None
        self._main_dataset = None
        self.training_dataset = None
        self.validation_dataset = None
        self.testing_dataset = None

        # Work out the label column indices.
        self.label_columns = label_columns
        if label_columns is not None:
            self.label_columns_indices = {name: i for i, name in
                                          enumerate(label_columns)}
        self.column_indices = None

        # Work out the window parameters.
        self.input_width = input_width
        self.label_width = label_width
        self.shift = shift

        self.total_window_size = input_width + shift

        self.input_slice = slice(0, input_width)
        self.input_indices = np.arange(self.total_window_size)[self.input_slice]

        self.label_start = self.total_window_size - self.label_width
        self.labels_slice = slice(self.label_start, None)
        self.label_indices = np.arange(self.total_window_size)[self.labels_slice]

    def submit_data(self, data):
        self._main_data = data

    def split_data(self, validation_ratio, testing_ratio):
        self._main_dataset = self.make_dataset(self._main_data)
        self._main_dataset.shuffle(25, reshuffle_each_iteration=True)

        total_length = len(self._main_dataset)

        if total_length == 0:
            print(total_length)
            raise RuntimeError("No appropriate data selected for training. Aborted!")

        self.testing_dataset = self.split_off_last(int(total_length * testing_ratio))
        self.validation_dataset = self.split_off_last(int(total_length * validation_ratio))
        self.training_dataset = self._main_dataset

    def split_off_last(self, split_amount):
        keeping = len(self._main_dataset) - split_amount
        taking = self._main_dataset.skip(keeping)
        self._main_dataset = self._main_dataset.take(keeping)
        return taking

    def add_more_test_data(self, data):
        to_add = self.make_dataset(data)
        to_add.shuffle(25, reshuffle_each_iteration=True)
        self.testing_dataset = self.testing_dataset.concatenate(to_add)

    def make_dataset(self, data):
        if type(data) is not list:
            return self.make_single_dataset(data)

        stream_iter = iter(data)
        ds = self.make_single_dataset(next(stream_iter))
        for stream in stream_iter:
            ds = ds.concatenate(self.make_single_dataset(stream))

        return ds

    def make_single_dataset(self, data):
        self.column_indices = {name: i for i, name in enumerate(data.columns)}

        data = np.array(data, dtype=np.float32)

        ds = tf.keras.preprocessing.timeseries_dataset_from_array(
            data=data,
            targets=None,
            sequence_length=self.total_window_size,
            sequence_stride=1,
            shuffle=True,
            batch_size=config.BATCH_SIZE, )

        ds = ds.map(self.split_window)

        return ds

    # splits each window into inputs and labels
    def split_window(self, features):
        inputs = features[:, self.input_slice, :]
        labels = features[:, self.labels_slice, :]

        if self.label_columns is not None:
            labels = tf.stack(
                [labels[:, :, self.column_indices[name]] for name in self.label_columns],
                axis=-1)

        # Slicing doesn't preserve static shape information, so set the shapes
        # manually. This way the `tf.data.Datasets` are easier to inspect.
        inputs.set_shape([None, self.input_width, None])
        labels.set_shape([None, self.label_width, None])

        return inputs, labels

    @property
    def test_example(self):
        return self.example_getting(self.testing_dataset)

    @property
    def train_example(self):
        return self.example_getting(self.training_dataset)

    def example_getting(self, where_from):
        return next(iter(where_from))

    def __repr__(self):
        return '\n'.join([
            f'Total window size: {self.total_window_size}',
            f'Input indices: {self.input_indices}',
            f'Label indices: {self.label_indices}',
            f'Label column name(s): {self.label_columns}'])

    def plot(self, plot_col, model=None, max_subplots=3):
        inputs, labels = self.test_example
        plt.figure(figsize=(12, 8))
        plot_col_index = self.column_indices[plot_col]
        max_n = min(max_subplots, len(inputs))
        for n in range(max_n):
            plt.subplot(max_n, 1, n + 1)
            plt.ylabel(f'{plot_col} [normed]')
            plt.plot(self.input_indices, inputs[n, :, plot_col_index],
                     label='Inputs', marker='.', zorder=-10)
            if self.label_columns:
                label_col_index = self.label_columns_indices.get(plot_col, None)
            else:
                label_col_index = plot_col_index
            if label_col_index is None:
                continue
            plt.scatter(self.label_indices, labels[n, :, label_col_index],
                        edgecolors='k', label='Labels', c='#2ca02c', s=64)
            if model is not None:
                predictions = model(inputs)
                sliced = predictions[n, :, label_col_index]
                shift_addition = self.total_window_size - len(sliced)
                to_show = [val for index, val in enumerate(sliced)
                           if index + shift_addition in self.label_indices]
                plt.scatter(self.label_indices, to_show,
                            marker='X', edgecolors='k', label='Predictions',
                            c='#ff7f0e', s=64)
            if n == 0:
                plt.legend()
        plt.xlabel('Time [30 mins]')
