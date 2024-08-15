<?php


namespace App\Geocoding;


class APIKeyManager
{
    private static $locationIQKey = "1b946e66453b76";
    private static $googlemapsKey = "AIzaSyDSDSHK4GFgfBPFdoyZ_mtD9ZiHEE4oBn0";
    private static $googlemapsDirectionKey = "AIzaSyDSDSHK4GFgfBPFdoyZ_mtD9ZiHEE4oBn0";
    private static $googlemapsDistanceMatrixKey = "AIzaSyDSDSHK4GFgfBPFdoyZ_mtD9ZiHEE4oBn0";
    private static $googlemapsPlacesKey = "AIzaSyDSDSHK4GFgfBPFdoyZ_mtD9ZiHEE4oBn0";
    private static $openrouteserviceKey = "5b3ce3597851110001cf6248aa5b23acdc1e46588df68e314d830c6b";
    private static $ParkWhizKey = "e7b574a31c8b483421a31f05fe0a62d91b1bb3b0";

    /**
     * @return string
     */
    public static function getLocationIQKey(): string
    {
        return self::$locationIQKey;
    }

    /**
     * @return string
     */
    public static function getGooglemapsKey(): string
    {
        return self::$googlemapsKey;
    }

    /**
     * @return string
     */
    public static function getGooglemapsDirectionKey(): string
    {
        return self::$googlemapsDirectionKey;
    }

    /**
     * @return string
     */
    public static function getGooglemapsDistanceMatrixKey(): string
    {
        return self::$googlemapsDistanceMatrixKey;
    }

    /**
     * @return string
     */
    public static function getGooglemapsPlacesKey(): string
    {
        return self::$googlemapsPlacesKey;
    }

    /**
     * @return string
     */
    public static function getOpenrouteserviceKey(): string
    {
        return self::$openrouteserviceKey;
    }

    /**
     * @return string
     */
    public static function getParkWhizKey(): string
    {
        return self::$ParkWhizKey;
    }


}