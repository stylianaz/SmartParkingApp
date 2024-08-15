<?php


namespace App\Geocoding;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;

class MatrixRequester
{
    private $locationsORS;
    private $originIndexORS;
    private $destinationIndexORS;

    private $locationOriginsGoogle;
    private $locationDestinationGoogle;

    private $lastValid;
    private $lastMatrix;
    private $lastCoordinatesOrigins = null;
    private $lastCoordinateDestinations = null;

    public function acceptOriginsAndDestinations($arrayOfCoordinatesOrigins, $arrayOfCoordinatesDestinations){
        if(!$this->isSameAsLastRequest($arrayOfCoordinatesOrigins, $arrayOfCoordinatesDestinations)
            or $this->lastValid === false){
            $this->lastValid = false;
            $this->lastCoordinatesOrigins = $arrayOfCoordinatesOrigins;
            $this->lastCoordinateDestinations = $arrayOfCoordinatesDestinations;
            $this->reallyAcceptOriginsAndDestinations($arrayOfCoordinatesOrigins, $arrayOfCoordinatesDestinations);
        }
    }

    private function reallyAcceptOriginsAndDestinations($arrayOfCoordinatesOrigins, $arrayOfCoordinatesDestinations){
        $this->lastCoordinatesOrigins = $arrayOfCoordinatesOrigins;
        $this->lastCoordinateDestinations = $arrayOfCoordinatesDestinations;

        $this->locationsORS = [];
        $this->originIndexORS = [];
        $this->destinationIndexORS = [];
        $this->locationOriginsGoogle = "";

        $counter = 0;
        foreach ($arrayOfCoordinatesOrigins as $coordinate) {
            array_push($this->locationsORS, [$coordinate['long'], $coordinate['lat']]);
            array_push($this->originIndexORS, $counter);

            $this->locationOriginsGoogle .= $coordinate['lat'] . ',' . $coordinate['long'] . '|';
            $counter++;
        }

        $this->locationOriginsGoogle = rtrim($this->locationOriginsGoogle, "|");
        $this->locationDestinationGoogle = "";
        foreach ($arrayOfCoordinatesDestinations as $coordinate) {
            array_push($this->locationsORS, [$coordinate['long'], $coordinate['lat']]);
            array_push($this->destinationIndexORS, $counter);

            $this->locationDestinationGoogle .= $coordinate['lat'] . ',' . $coordinate['long'] . '|';
            $counter++;
        }
        $this->locationDestinationGoogle = rtrim($this->locationDestinationGoogle, "|");
    }

    private function isSameAsLastRequest($arrayOfCoordinatesOrigins, $arrayOfCoordinatesDestinations){
        return $this->lastCoordinatesOrigins === $arrayOfCoordinatesOrigins and
            $this->lastCoordinateDestinations === $arrayOfCoordinatesDestinations;
    }

    /**
     * @throws Exception
     */
    public function getDistanceMatrix(): array
    {
        if($this->lastValid){
            return $this->lastMatrix;
        }

        $matrix = $this->getMatrixFromORS();
        if($matrix === false) {
            $matrix = $this->getMatrixFromGoogle();
        }
        if($matrix === false){
            $this->lastValid = false;
            throw new Exception("Failed to get distance matrix from any API.");
        }

        $this->lastMatrix = $matrix;
        $this->lastValid = 1;
        return $matrix;
    }

    private function getMatrixFromORS(){
        $matrix = Array();
        $client = new Client();

        $headers = ['Authorization' => APIKeyManager::getOpenrouteserviceKey(), 'Content-Type' => 'application/json'];
        $body = stream_for(json_encode([
            "locations" => $this->locationsORS,
            "destinations" => $this->destinationIndexORS,
            "metrics" => ["distance", "duration"],
            "sources" => $this->originIndexORS
            ]));
        $request = new Request('POST', 'https://api.openrouteservice.org/v2/matrix/driving-car', $headers, $body);
        $response = $client->send($request);

        $code = $response->getStatusCode();
        $json = json_decode($response->getBody(), true);

        if (isset($json) && is_array($json) && isset($json['durations']) && isset($json['distances']) && ($code == 200)) {
            $matrix['durations'] = $json['durations'];
            $matrix['distances'] = $json['distances'];
            return $matrix;
        }
        return false;
    }

    private function getMatrixFromGoogle(){
        //https://developers.google.com/maps/documentation/distance-matrix/usage-and-billing?hl=en_US
        //https://console.cloud.google.com/google/maps-apis/apis/distance-matrix-backend.googleapis.com/quotas?project=smartparking-211418&duration=PT1H
        //https://developers.google.com/maps/documentation/distance-matrix/intro

        $client = new Client();
        $response = $client->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'query' => [
                'origins' => $this->locationOriginsGoogle,
                        'destinations' => $this->locationDestinationGoogle,
                        'key' => APIKeyManager::getGooglemapsDistanceMatrixKey()]
        ]);

        $code = $response->getStatusCode();
        $json = json_decode($response->getBody(), true);
        if (isset($json) && is_array($json) && isset($json['rows']) && ($code == 200)) {
            $duration = Array();
            $destinations = Array();
            $json = $json['rows'];
            foreach ($json as $rows) {
                $durationRow = Array();
                $destinationRow = Array();
                foreach ($rows['elements'] as $columns) {
                    $durationRow[] = $columns['duration']['value'];
                    $destinationRow[] = $columns['distance']['value'];
                }
                $duration[] = $durationRow;
                $destinations[] = $destinationRow;
            }
            $matrix['durations'] = $duration;
            $matrix['distances'] = $destinations;
            return $matrix;
        }
        return false;
    }
}