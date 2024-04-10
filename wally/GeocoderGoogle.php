<?php

class GeocoderGoogle extends Geocoder {

    function search($f3, $query) {
        $db = $this->db;
        $query = $f3->get("GET.query");
        //TODO: Errorhandling $query = empty($args["query"]) ? NULL : $args["query"];
        $query = urlencode($query);

        $url = "https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input=".$query."&inputtype=textquery&fields=place_id%2Cformatted_address%2Cname%2Crating%2Copening_hours%2Cgeometry&key=".$_ENV["GOOGLE_API_KEY"];
        $data = getJson($url);

        $results = [];
        foreach ($data["candidates"] as $candidate) {
            $details = $this->getDetails($candidate["place_id"]);

            $address = new Address();
            $address->lat = $details["geometry"]["location"]["lat"];
            $address->lng = $details["geometry"]["location"]["lng"];
            $address->sourceGeocoder = "googlemaps";
            $address->sourceId = $candidate["place_id"] ?? NULL;
            $address->name = $details["name"] ?? NULL;

            foreach ($details["address_components"] as $addressComponent) {
                switch ($addressComponent["types"][0]) {
                    case "street_number":
                        $address->housenumber = $addressComponent["long_name"];
                        break;
                    case "route":
                        $address->street = $addressComponent["long_name"];
                        break;
                    case "locality":
                        $address->city = $addressComponent["long_name"];
                        break;
                    case "country":
                        $address->country = $addressComponent["long_name"];
                        break;
                    case "postal_code":
                        $address->postcode = $addressComponent["long_name"];
                        break;

                }
            }
            $address->category = str_replace("_", " ", ucfirst($details["types"][0]));
            $results[] = $address;
        }
        return $results;
    }

    function reverse($f3, $lat, $lng) {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lng."&key=".$_ENV["GOOGLE_API_KEY"];
        $data = getJson($url);

        $details = $data["results"][0];

        $address = new Address();
        $address->lat = $details["geometry"]["location"]["lat"];
        $address->lng = $details["geometry"]["location"]["lng"];
        $address->sourceGeocoder = "googlemaps";
        $address->sourceId = $details["place_id"] ?? NULL;

        foreach ($details["address_components"] as $addressComponent) {
            switch ($addressComponent["types"][0]) {
                case "street_number":
                    $address->housenumber = $addressComponent["long_name"];
                    break;
                case "route":
                    $address->street = $addressComponent["long_name"];
                    break;
                case "locality":
                    $address->city = $addressComponent["long_name"];
                    break;
                case "country":
                    $address->country = $addressComponent["long_name"];
                    break;
                case "postal_code":
                    $address->postcode = $addressComponent["long_name"];
                    break;

            }
        }
        $address->name = $address->getAddress();
        $address->category = str_replace("_", " ", ucfirst($details["types"][0]));
        return $address;
    }

    function getDetails($placeId) {
        $url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=".$placeId."&key=".$_ENV["GOOGLE_API_KEY"];
        $data = getJson($url);
        return $data["result"];
    }

    /*

    function findPlace($f3, $args) {
        $lat = $f3->get("GET.lat");
        $lng = $f3->get("GET.lng");
        $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$lat.",".$lng."&radius=1000&type=restaurant&key=".$_ENV["GOOGLE_API_KEY"];
        $data = getJson($url);

        $results = array();
        foreach($data["results"] as $result) {
            $entry = array(
                "name" => $result["name"],
                "address" => $result["vicinity"],
                "lat" => $result["geometry"]["location"]["lat"],
                "lng" => $result["geometry"]["location"]["lng"],
                "reference" => $result["place_id"],
                "referenceType" => "google",
                "type" => $result["types"][0],
            );
            array_push($results, $entry);
        }

        $f3->set("results", $results);
        echo Template::instance()->render("results.html");
    }
    */
}


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
        echo $response;
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
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
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
