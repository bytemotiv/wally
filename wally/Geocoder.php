<?php

class Address {
    public $name;
    public $lat;
    public $lng;

    public $street;
    public $housenumber;
    public $postcode;
    public $city;
    public $country;

    public $category;

    public $sourceId; // z.B. [place_id] place_12345
    public $sourceGeocoder; // e.g. "nominatim"

    public function getAddress() {
        $buffer = "";
        $buffer .= $this->street ?? "Unknown Street";
        if ($this->housenumber) { $buffer .= " ".$this->housenumber; }
        $buffer .= "<br>";
        if ($this->postcode) { $buffer .= $this->postcode." "; }
        $buffer .= $this->city ?? "Unknown City";
        if ($this->country) { $buffer .= ", ".$this->country; }
        return $buffer;
    }

    public function __get($name) {
        if ($name == "address") {
            $buffer = "";
            $buffer .= $this->street ?? "Unknown Street";
            if ($this->housenumber) { $buffer .= " ".$this->housenumber; }
            $buffer .= "<br>";
            if ($this->postcode) { $buffer .= $this->postcode." "; }
            $buffer .= $this->city ?? "Unknown City";
            if ($this->country) { $buffer .= ", ".$this->country; }
            return $buffer;

        }
    }

}

class Geocoder extends Controller {

    function search($f3, $query) {
        // returns an array of Address objects
    }

    function reverse($f3, $lat, $lng) {
        // returns an Address object
    }

    /*
    function getJson($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if ($response === false) {
            return array(
                "error" => "cURL error: ".curl_error($ch)
            );
        }
        curl_close($ch);

        $jsonData = json_decode($response, true);
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            return array(
                "error" => "Error decoding JSON: ".json_last_error_msg()
            );
        } else {
            return $jsonData;
        }
    }

    function postJson($url, $data=[], $headers=[]) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);

        if ($response === false) {
            return array(
                "error" => "cURL error: ".curl_error($ch)
            );
        }
        curl_close($ch);
        $jsonData = json_decode($response);

        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            return array(
                "error" => "Error decoding JSON: ".json_last_error_msg()
            );
        } else {
            return $jsonData;
        }
    }
    */
}
