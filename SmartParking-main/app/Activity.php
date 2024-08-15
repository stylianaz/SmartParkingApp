<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    //
    public function user() {
        return $this->belongsTo( 'App\User' ,'user_id','id');        
    }   
    
    //public function parkingtype() {
    //    return $this->belongsTo( 'App\ParkingType' ,'parkingtype_id','id');        
    //}   
  
    public function place() {
        return $this->belongsTo( 'App\Places' ,'places_id','id');        
    }     
}
