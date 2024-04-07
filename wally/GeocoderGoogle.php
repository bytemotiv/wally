<?php

class GeocoderGoogle extends Geocoder {

    function search($f3, $args) {
        $query = empty($args["query"]) ? NULL : $args["query"];
        $query = urlencode($query);

        $url = "https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input=".$query."&inputtype=textquery&fields=place_id%2Cformatted_address%2Cname%2Crating%2Copening_hours%2Cgeometry&key=".$_ENV["GOOGLE_API_KEY"];
        $data = getJson($url);

        var_dump($data);
    }

    function details($f3, $args) {
        $placeId = empty($args["placeId"]) ? NULL : $args["placeId"];
        $placeId = urlencode($placeId);
        $placeId = "ChIJ4QQq_H00GQ0R1PhmnSP7o3E";

        $url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=".$placeId."&key=".$_ENV["GOOGLE_API_KEY"];
        $data = getJson($url);

        var_dump($data);
    }

    function reverse($f3, $args) {
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
