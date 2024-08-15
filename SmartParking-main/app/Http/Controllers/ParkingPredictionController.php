<?php

namespace App\Http\Controllers;

use App\Prediction;
use Illuminate\Http\Request;

class ParkingPredictionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getOldPredictionSite(Request $request)
    {
        return view('oldPredictionSite');
    }

    public function getPrediction(Request $request)
    {
        if(!$this->isValidPredictionRequest($request)){
            return response(null, 400);
        }
        return $this->getPredictionInternal($request);
    }

    public function getPredictionInternal($details){
        $output = null;
        $ret_val = null;
        //in the Dockerfile we do a "make altinstall" meaning the python3.9 binary has to hunted out
        $command = "cd ../new_parking_prediction; /usr/local/bin/python3.9 ./make_prediction.py $details->carpark_id $details->prediction_time";

        $time = time();
        exec($command, $output, $ret_val);

        $prediction = new Prediction;
        $prediction->carpark_id = $details->carpark_id;
        $prediction->user_id = $details->user_id;
        $prediction->predicted_spaces = $output[0];
        $prediction->lookback_used = false;
        $prediction->local_demographics_used = false;
        $prediction->predicted_for_time = $details->prediction_time - 60 * 60 * 3; //Converting back to UTC time
        $prediction->predicted_at_time = $time;
        $prediction->save();

        return $this->getSuccessResponse($output[0]);
    }

    private function isValidPredictionRequest(Request $request){
        return !empty($request->carpark_id) and !empty($request->prediction_time) and !empty($request->user_id);
    }

    private function getSuccessResponse($prediction){
        $response = response()->json(["prediction" => $prediction]);
        error_log($response->status());
        return $response;
    }
}