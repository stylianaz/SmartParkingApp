import sys
import ml_interface
import ml_logger

def predict(carpark_id, timestamp):
    print(ml_interface.get_prediction(carpark_id, timestamp))

# called by the PredictionController for the laravel site - the printed value gets read and
# sent back to the JS frontend in response to an AJAX request
predict(sys.argv[1], sys.argv[2])