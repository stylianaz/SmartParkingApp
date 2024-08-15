import tensorflow as tf


# this is extremely important - all tunable training parameters and model designs should be here
# the only other file where parameters are gathered is data_gathering.data_config

def mult_zip(list_a, list_b):
    zipped = [list(zip([a] * len(list_b), list_b)) for a in list_a]
    return [z_tuple for z_list in zipped for z_tuple in z_list]


ADDED_FEATURE_COLUMNS = ["day_sin", "day_cos", "week_sin", "week_cos"]
OUTPUT_FEATURES = ["spaces_occupied"]
all_features = OUTPUT_FEATURES + ADDED_FEATURE_COLUMNS
num_output_features = len(OUTPUT_FEATURES)

BASE_WIDTH = 48 * 7
LABEL_WIDTH = 48
SHIFT = 48

HOLDBACK = 0
CARPARKS_TO_TRAIN_ON = mult_zip([0], range(8)) + mult_zip([1], range(1, 6)) + mult_zip([1], [11, 13]) + mult_zip([1], range(486, 490))
ADDITIONAL_TESTING_CARPARKS = None #  doesn't currently work
COMMENTS = ""

MAX_EPOCHS = 50
BATCH_SIZE = 32
CONV_WIDTH = 3



# this baseline is polymorphic - it works for single and multi-shot prediction
# just returns the last seen value in single-shot case
# for multi-shot returns the data seen at the corresponding point in the input series
class Baseline(tf.keras.Model):
    def __init__(self, label_index=0, label_width=LABEL_WIDTH):
        super().__init__()
        self.label_width = label_width
        self.label_index = label_index

    def call(self, inputs):
        if self.label_index is None:
            return inputs
        if LABEL_WIDTH <= inputs.shape[1]:
            result = inputs[:, :self.label_width, self.label_index]
        else:
            result = inputs[:, :self.label_width, self.label_index]
        return result[:, :, tf.newaxis]


# the following non "multi" models make single-shot predictions - for time now + shift * sample interval
# sample interval can be found in data_gathering.data_config
linear_model = tf.keras.Sequential([
    tf.keras.layers.Dense(units=64, activation="relu"),
    tf.keras.layers.Dense(units=64, activation="relu"),
    tf.keras.layers.Dense(units=1)
])

# validation doesn't work for convolution when there are too few training and testing examples
conv_model = tf.keras.Sequential([
    tf.keras.layers.Conv1D(filters=32,
                           kernel_size=(CONV_WIDTH,),
                           activation='relu'),
    tf.keras.layers.Dense(units=32, activation='relu'),
    tf.keras.layers.Dense(units=1),
])

lstm_model = tf.keras.models.Sequential([
    # Shape [batch, time, features] => [batch, time, lstm_units]
    tf.keras.layers.LSTM(50, return_sequences=True),
    # Shape => [batch, time, features]
    tf.keras.layers.Dense(units=32, activation='relu'),
    tf.keras.layers.Dense(units=1)
])


# the following "multi" models make predictions at every interval over the entire label_width
def get_multi_dense_model(label_width):
    return tf.keras.Sequential([
        # Take the last time step.
        # Shape [batch, time, features] => [batch, 1, features]
        tf.keras.layers.Lambda(lambda x: x[:, -1:, :]),
        # Shape => [batch, 1, dense_units]
        tf.keras.layers.Dense(512, activation='relu'),
        # Shape => [batch, out_steps*features]
        tf.keras.layers.Dense(label_width*num_output_features,
                              kernel_initializer=tf.initializers.zeros()),
        # Shape => [batch, out_steps, features]
        tf.keras.layers.Reshape([label_width, num_output_features])
    ])


def get_multi_conv_model(label_width):
    return tf.keras.Sequential([
        # Shape [batch, time, features] => [batch, CONV_WIDTH, features]
        tf.keras.layers.Lambda(lambda x: x[:, -CONV_WIDTH:, :]),
        # Shape => [batch, 1, conv_units]
        tf.keras.layers.Conv1D(256, activation='relu', kernel_size=CONV_WIDTH),
        # Shape => [batch, 1,  out_steps*features]
        tf.keras.layers.Dense(label_width*num_output_features,
                              kernel_initializer=tf.initializers.zeros()),
        # Shape => [batch, out_steps, features]
        tf.keras.layers.Reshape([label_width, num_output_features])
    ])


def get_multi_lstm_model(label_width):
    return tf.keras.Sequential([
        # Shape [batch, time, features] => [batch, lstm_units].
        tf.keras.layers.LSTM(62, return_sequences=False),
        # Shape => [batch, out_steps*features].
        tf.keras.layers.Dense(label_width*num_output_features,
                              kernel_initializer=tf.initializers.zeros()),
        # Shape => [batch, out_steps, features].
        tf.keras.layers.Reshape([label_width, num_output_features])
    ])


names = {"baseline": Baseline ,"linear": get_multi_dense_model, "CNN": get_multi_conv_model, "LSTM": get_multi_lstm_model}
MODEL = get_multi_dense_model(LABEL_WIDTH)


def get_input_width(base_width=BASE_WIDTH, conv_width=CONV_WIDTH, model=MODEL):
    try:
        is_conv = type(model.layers[1]) == tf.keras.layers.Conv1D
    except IndexError:
        is_conv = False
    return base_width + conv_width - 1 if is_conv else base_width
