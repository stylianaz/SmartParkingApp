<?php

namespace App;

class Utilities{
    //Assumes all merging arrays have the keys of the first array at least
    public static function mergeArrays($arraysToMerge){
        $merged = array();
        foreach($arraysToMerge[0] as $key => $value){
            $combinedRow = array();
            foreach($arraysToMerge as $array){
                $combinedRow = array_merge($combinedRow, $array[$key]);
            }
            $merged[$key] = $combinedRow;
        }
        return $merged;
    }

    public static function convertCollectionToArrayOfArrays($dbResult){
        $result = array();
        foreach($dbResult as $row){
            $result[$row->id] = (array)$row;
        }
        return $result;
    }
}