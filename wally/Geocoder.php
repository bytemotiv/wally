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
}
