<?php

namespace App\Http\Controllers;

use App\Places;
use App\StoredQueries;
use Illuminate\Http\Request;

class ParkingController extends Controller
{
    public function getCarpark(Request $request){
        if(!$this->isValidCarparkRequest($request)){
            return response(null, 400);
        }
        $carpark = StoredQueries::getCarparkDetailsFromId($request->carpark_id)->first();

        $json_rep = [];
        foreach ((array)$carpark as $key => $value){
            $json_rep[$key] = $value;
        }
        $json_rep['placesCosts'] = Places::find($request->carpark_id)->placesCosts;
        return response()->json($json_rep);
    }

    private function isValidCarparkRequest($request){
        return !empty($request->carpark_id);
    }
}
