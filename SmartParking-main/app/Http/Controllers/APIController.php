<?php

namespace App\Http\Controllers;

use App\CachingTime;
use App\Fuzzy\FuzzySmartParkingCalculation;
use App\Geocoding\Geocoder;
use App\requestResults;
use App\StoredQueries;
use App\Target;
use App\User;
use App\userRequest;
use App\Utilities;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class APIController extends Controller
{

    public $successStatus = 200;
    //login using oAuth protocol by passport

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if (User::checkUserForAPI($request->identity, $request->password)) {
            $user = Auth::user();
            $application = empty($request->application) ? 'SMARTPARKING' : $request->application;
            $success['token'] = $user->createToken($application)->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function authorization(Request $request)
    {
        return $this->login($request);
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $application = empty(request('application')) ? 'SMARTPARKING' : request('application');
        $success['token'] = $user->createToken($application)->accessToken;
        $success['name'] = $user->name;

        return response()->json(['success' => $success], $this->successStatus);
    }

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function getDetails()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    public function getAddressAutocomplete(Request $request)
    {
        try {
            if (!empty($request->address)) {
                $getResultsClosure = function () use ($request){
                    return $this->getAutocompleteResults($request);
                };
                $coordinatesNames = Cache::remember($request->address.$request->waytoask, CachingTime::MEDIUM, $getResultsClosure);

                $returnArray = Array();
                foreach ($coordinatesNames as $key => $value) {
                    $row = Array();
                    $row['id'] = $key;
                    $row['value'] = $key;
                    $row['label'] = $value['Name'];//." ".$value['source'];
                    $row['lat'] = $value['lat'];
                    $row['long'] = $value['long'];
                    $returnArray[$key] = $row;
                }
                return response('{"content":' . json_encode($returnArray) . ',"status":"success"}', 200);
            } else {
                return response('{"content":' . ',"status":"success"}', 200);
            }
        } catch (Exception $ex) {
            return response('{"content":' . ',"status":"success"}', 200);
        }
    }

    public function getAutocompleteResults($request): array{
        $geo = new Geocoder();
        if (isset($request->waytoask) && !empty($request->waytoask)) {
            $coordinatesNames = $geo->getLatLogFromAddressMoreAccurate($request->address);
        } else {
            $coordinatesNames = $geo->getAutocompleteListForOpenRouteServicewithNominatimandGeonames($request->address);
        }
        return $coordinatesNames;
    }

    public function getAddressfromCoordinates(Request $request)
    {
        $geo = new Geocoder();
        //print_r($request->lat." ".$request->long);
        //die();
        if (!empty($request->lat) && !empty($request->long)) {
            $address = $geo->getFromLatLogAddress($request->lat, $request->long);
            //print_r($address);
            if (empty($address)) {
                return response('{"content":' . ',"status":"success"}', 200);
            }
            return response('{"content":' . json_encode($address[0]) . ',"status":"success"}', 200);
        } else {
            return response('{"content":' . ',"status":"success"}', 200);
        }
    }

    public function getParkings(Request $request)
    {
        $getCarparksClosure = function () use ($request){
            $geo = new Geocoder();
            return $geo->getParkingsAroundALatLong($request->lat, $request->long, 1, $request->user_id,
                User::isAdminById($request->user_id) ? "admin" : "user");
        };
        return $this->getCarparksUsingClosure($request, $getCarparksClosure);
    }

    public function getParkingsOwner(Request $request)
    {
        $getCarparksClosure = function () use ($request){
            $geo = new Geocoder();
            return $geo->getParkingsAroundALatLong($request->lat, $request->long, 1, $request->user_id, "owner");
        };
        return $this->getCarparksUsingClosure($request, $getCarparksClosure);
    }

    private function getCarparksUsingClosure($request, $getCarparksClosure){
        if (!empty($request->lat) && !empty($request->long)) {
            $coordinatesNames = Cache::remember($request->lat.$request->long.$request->user_id, CachingTime::SHORT, $getCarparksClosure);
            return response('{"content":' . json_encode($coordinatesNames) . ',"status":"success"}', 200);
        } else {
            return response('{"content":' . $request->lat . " " . $request->long . ',"status":"success"}', 200);
        }
    }

    public function getWeightedParkings(Request $request)
    {
        if ($this->isWeightedParkingRequestValid($request)) {
            return $this->getWeightedParkingResponse($request);
        } else {
            return response('{"content":' . $request->lat . " " . $request->long . ',"status":"success"}', 200);
        }
    }

    private function isWeightedParkingRequestValid($request): bool{
        return !empty($request->Userlat) && !empty($request->Userlong) && !empty($request->Destinationlat)
                && !empty($request->Destinationlog) && !empty($request->predictionForTime) && !empty($request->user_id)
                && (isset($request->selection) && is_array($request->selection) && count($request->selection) > 0);
    }

    private function getWeightedParkingResponse(Request $request){
        error_log("starting");
        $start = new DateTime();
        $selectionpriority = $request->selection;

        $coordinates = $this->getCandidates($request);

        $fuzzyStart = new DateTime();
        $coordinates = $this->performFuzzyCalculation($coordinates, $selectionpriority);
        $fuzzyend = new DateTime();
        $end = new DateTime();

        //save user request
        $userRequest = new userRequest();
        $userRequest->userlat = $request->Userlat;
        $userRequest->userlong = $request->Userlong;
        $userRequest->userloc = \DB::raw("GeomFromText('POINT(" . $request->Userlat . " " . $request->Userlong . ")')");
        $userRequest->destlat = $request->Destinationlat;
        $userRequest->destlong = $request->Destinationlog;
        $userRequest->destloc = \DB::raw("GeomFromText('POINT(" . $request->Destinationlat . " " . $request->Destinationlog . ")')");
        $userRequest->user_id = $request->user_id;
        $userRequest->totaltime = $end->diff($start)->format("%s");
        $userRequest->fuzzytime = $fuzzyend->diff($fuzzyStart)->format("%s");
        $userRequest->time = NOW();
        $userRequest->save();
        $userRequest->requestparameters()->attach($selectionpriority[0], ['order' => 0]);
        $userRequest->requestparameters()->attach($selectionpriority[1], ['order' => 1]);
        $userRequest->requestparameters()->attach($selectionpriority[2], ['order' => 2]);

        //assign url and save results
        $count = 0;
        foreach ($coordinates as $key => $value) {
            unset($coordinates[$key]['1']);
            unset($coordinates[$key]['2']);
            unset($coordinates[$key]['3']);
            unset($coordinates[$key]['4']);
            unset($coordinates[$key]['5']);
            unset($coordinates[$key]['6']);
            unset($coordinates[$key]['7']);
            if ($count < 9) {
                $coordinates[$key]['url'] = asset('images/orderedList' . $count . '.png');
            }
            ++$count;
            //now save the results in db for statistical purpose
            $req = new requestResults();
            $req->name = empty($coordinates[$key]['name']) ? 'No Name' : $coordinates[$key]['name'];
            $req->distanceDest = $coordinates[$key]['distancedestinationPossition'];
            $req->distanceDestNorm = $coordinates[$key]['percentagedistancedestinationPossition'];
            $req->distanceUser = $coordinates[$key]['distancecurrentPossition'];
            $req->distanceUserNorm = $coordinates[$key]['percentagedistancecurrentPossition'];
            $req->durationDest = $coordinates[$key]['durationdestinationPossition'];
            $req->durationDestNorm = $coordinates[$key]['percentagedurationdestinationPossition'];
            $req->durationUser = $coordinates[$key]['durationcurrentPossition'];
            $req->durationUserNorm = $coordinates[$key]['percentagedurationcurrentPossition'];
            $req->costNorm = $coordinates[$key]['percentagecost'];
            $req->score = $coordinates[$key]['FuzzyValueTotal'];
            $req->places_id = $coordinates[$key]['id'];
            $req->user_id = $request->user_id;
            $req->save();
            $req->userrequests()->attach($userRequest->id);
            //$req->requestparameters()->attach($selectionpriority[0], ['order' => 1]);
        }

        //save user target
        $target = new Target();
        $target->lat = $request->Userlat;
        $target->long = $request->Userlong;
        $target->loc = \DB::raw("GeomFromText('POINT(" . $request->Userlat . " " . $request->Userlong . ")')");
        $target->user_id = $request->user_id;
        $target->time = NOW();
        $target->save();

        return response('{"content":' . json_encode($coordinates) . ',"status":"success"}', 200);
    }

    // Get and prepare all carparks which will be evaluated using fuzzy logic
    private function getCandidates(Request $request): array{
        $geo = new Geocoder();
        $userDestination = Array('lat' => $request->Destinationlat, 'long' => $request->Destinationlog);
        $userLocation = Array('lat' => $request->Userlat, 'long' => $request->Userlong);
        $startingCoordinates = Array($userLocation, $userDestination);

        //TODO: skip this step if request selection does not contain parking space availability paramater
        $this->preparePredictionsForAllFuzzyCandidates($request);

        $candidates = $geo->getParkingsAroundALatLong($request->Destinationlat, $request->Destinationlog, 1, $request->user_id, "user");
        $matrix = $geo->getDistanceMatrix($startingCoordinates, array_merge($candidates, $startingCoordinates));
        $counter = 0;

        foreach ($candidates as $key => $value) {
            $candidates[$key]['distancecurrentPossition'] = empty($matrix['distances'][0][$counter]) ? -1 : $matrix['distances'][0][$counter];
            $candidates[$key]['distancedestinationPossition'] = $candidates[$key]['Distance'];//the person will walk so take point distance (not car) empty($matrix['distances'][1][$counter]) ? -1 : $matrix['distances'][1][$counter];
            $candidates[$key]['durationcurrentPossition'] = empty($matrix['durations'][0][$counter]) ? -1 : $matrix['durations'][0][$counter];
            $candidates[$key]['durationdestinationPossition'] = ($candidates[$key]['Distance'])/(12.5);// in seconds person runs 40 km/hour duration base on the walking speed of 20 km /h (not car with roads) empty($matrix['durations'][1][$counter]) ? -1 : $matrix['durations'][1][$counter];
            if (empty($matrix['durations'][1][$counter]) || empty($matrix['durations'][0][count($matrix) - 1])) {
                $candidates[$key]['duration'] = -1;
            } else {
                $candidates[$key]['duration'] = $matrix['durations'][1][$counter] + $matrix['durations'][0][count($matrix) - 1];
            }
            $candidates[$key]['occupancyPercentage'] = 100 * (1 - $value['predicted_spaces'] / $value['capacity']);
            ++$counter;
        }
        return $candidates;
    }

    private function preparePredictionsForAllFuzzyCandidates(Request $request){
        $candidates = StoredQueries::getNearbyCarparks($request->Destinationlat, $request->Destinationlog, 1);
        $candidates = Utilities::convertCollectionToArrayOfArrays($candidates);

        $counter = 0;
        foreach($candidates as $candidate){
            app('App\Http\Controllers\ParkingPredictionController')
                ->getPredictionInternal((object)["carpark_id" => $candidate['id'],
                                       "prediction_time"=> $request->predictionForTime,
                                       "user_id"=> $request->user_id]);
            ++$counter;
        }
        error_log("Made ".$counter." predictions to prepare for fuzzy suggestion request.");
    }

    private function performFuzzyCalculation($coordinates, $selectionpriority){
        $statistics = Array();
        $toNormalise = ["distancecurrentPossition", "distancedestinationPossition", "cost", "durationcurrentPossition",
                        "durationdestinationPossition", "duration", "occupancyPercentage"];
        foreach($toNormalise as $factor){
            $max = $this->getMax($coordinates, $factor);
            $statistics['max'.$factor] = empty($max) ? 1 : $max;
            if ($statistics['max'.$factor] < 0) {
                $statistics['max'.$factor] = 1;
            }
        }

        foreach ($coordinates as $key => $_) {
            $counter = 1;
            foreach($toNormalise as $factor){
                $coordinates[$key]['percentage'.$factor] = ($coordinates[$key][$factor] / $statistics['max'.$factor]) * 100;
                $coordinates[$key][(string)$counter] = $coordinates[$key]['percentage'.$factor];
                if ($coordinates[$key][(string)$counter] < 0) {
                    $coordinates[$key][(string)$counter] = 100;
                    $coordinates[$key]['percentage'.$factor] = 100;
                }
                ++$counter;
            }

            $arrayofParameters['PARAMETER1'] = $coordinates[$key][$selectionpriority[0]];
            $arrayofParameters['PARAMETER2'] = $coordinates[$key][$selectionpriority[1]];
            $arrayofParameters['PARAMETER3'] = $coordinates[$key][$selectionpriority[2]];

            $fuzzycalculation = new FuzzySmartParkingCalculation();
            //add in the calculated fuzzy value the validity of the parking in order to show valid parking on top
            $coordinates[$key]['FuzzyValueTotal'] = $fuzzycalculation->calculateWithParameters($arrayofParameters) + $coordinates[$key]['validity'];
        }

        //sort array by fuzzy value
        $fuzzy = array();
        foreach ($coordinates as $key => $row) {
            $fuzzy[$key] = $row['FuzzyValueTotal'];
        }
        array_multisort($fuzzy, SORT_DESC, $coordinates);

        return $coordinates;
    }

    public function getMax($array, $index)
    {
        $max = 0;
        foreach ($array as $k => $v) {
            $max = max(array($max, $v[$index]));
        }
        return $max;
    }

    public function getMin($array, $index)
    {
        $min = 100000;
        foreach ($array as $k => $v) {
            $min = min(array($min, $v[$index]));
        }
        return $min;
    }

}
