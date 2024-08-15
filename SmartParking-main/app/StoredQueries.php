<?php
namespace App;

use Illuminate\Support\Facades\DB;

class StoredQueries{
    public static function getCarparkDetailsFromId($id){
        return getCarparksDetailsFromIdsQuery(array($id))->take(1)->get();
    }

    public static function getCarparksDetailsFromIds($ids){
        return getCarparksDetailsFromIdsQuery($ids)->get();
    }

    public static function getCarparksFilteredByRole($ids, $role, $userId){
        $query = decorateQueryWithIdSelection(DB::table('places'), $ids);
        if($role == 'admin'){
            return $query->selectRaw('`places`.`id`', array($userId))->get();
        }
        else if($role == 'provider'){
            $query = $query->where('places.provider_id', '=', $userId);
            return $query->selectRaw(
                '`places`.`id`, `isBookmarkedThePlaceForUser` ( ?, `places`.`id` ) as IsBookMarkForUser', array($userId))->get();
        }
        $query = $query->where('places.avaliable', '=', 1);
        return $query->selectRaw(
            '`places`.`id`, `isBookmarkedThePlaceForUser` ( ?, `places`.`id` ) as IsBookMarkForUser', array($userId))->get();
    }

    public static function getNearbyCarparks($lat, $long, $radius){
        return DB::table('places')
            ->whereRaw('earth_circle_distance( POINT ( ?, ? ), loc ) < ?', array($lat, $long, $radius))
            ->orderByRaw('earth_circle_distance( Point ( ?, ? ), loc ) ASC', array($lat, $long))
            ->selectRaw('`places`.`id`, `earth_circle_distance` ( POINT ( ? , ? ), `places`.`loc` ) AS Distance',
                array($lat, $long))->get();
    }
}

function getCarparksDetailsFromIdsQuery($ids){
    $query = DB::table('places')
        ->selectRaw(carparkRawSelectionString())
        ->leftJoinSub(getLatestPredictionSubQuery(), 'latest_predictions',
            'latest_predictions.carpark_id', '=', 'places.id')
        ->leftJoinSub(getLatestOccupancySubQuery(), 'latest_occupancy',
            'places.id', '=', 'latest_occupancy.carpark_id')
        ->join('parking_types', 'places.parkingtype_id', '=', 'parking_types.id');

    return decorateQueryWithIdSelection($query, $ids);
}

function decorateQueryWithIdSelection($query, $ids){
    $query = $query->where('places.id', '=', $ids[0]);
    foreach($ids as $id){
        $query = $query->orWhere('places.id', '=', $id);
    }
    return $query;
}

function getLatestPredictionSubQuery(){
    $latestTimestampSubquery = DB::table('predictions')
        ->select('carpark_id', DB::raw('MAX(predicted_at_time) as latest'))
        ->groupBy('carpark_id');

    $latestPredictionSubquery = DB::table('predictions')
        ->select('predictions.carpark_id', 'predictions.predicted_spaces', 'predictions.predicted_for_time')
        ->joinSub($latestTimestampSubquery, 'l', function($join){
            $join->on('predictions.carpark_id', '=', 'l.carpark_id')->on('predictions.predicted_at_time', '=', 'l.latest');
        });

    return $latestPredictionSubquery;
}

function getLatestOccupancySubQuery(){
    $latestTimestampSubquery = DB::table('occupancy')
        ->select('carpark_id', DB::raw('MAX(timestamp) as latest'))
        ->groupBy('carpark_id');

    $latestOccupancySubquery = DB::table('occupancy')
        ->select('occupancy.carpark_id', 'occupancy.spaces_available')
        ->joinSub($latestTimestampSubquery, 'l', function($join){
            $join->on('occupancy.carpark_id', '=', 'l.carpark_id')->on('occupancy.timestamp', '=', 'l.latest');
        });

    return $latestOccupancySubquery;
}

function carparkRawSelectionString(): string
{
    return '`parking_types`.`name`, `places`.`name`, `places`.`lat`, `places`.`long`, ' .
        '`places`.`disabledcount`, `places`.`avaliable`, `places`.`user_id`, ' .
        '`getCostforPlaceCalc` (places.id) as cost, `places`.`reportedcount`, `places`.`validity`, ' .
        '`places`.`capacity`, `places`.`time`, `places`.`maximumduration`, `places`.`occupied`, ' .
        '`places`.`comments`, `places`.`parkingtype_id`, `places`.`id`, ' .
        '`places`.`provider_id`, `latest_occupancy`.`spaces_available`,' .
        '`latest_predictions`.`predicted_spaces`, `latest_predictions`.`predicted_for_time`';
}