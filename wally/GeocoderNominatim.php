<?php

class GeocoderNominatim extends Geocoder {

    function search($f3, $query) {
        $db = $this->db;
        $query = $f3->get("GET.query");
        $userAgent = $f3->get("SERVER.HTTP_USER_AGENT");
        //TODO: Errorhandling $query = empty($args["query"]) ? NULL : $args["query"];

        $url = "https://nominatim.openstreetmap.org/search?q=".urlencode($query)."&addressdetails=1&format=geocodejson&limit=10";
        $rawResults = $this->getJson($url, $userAgent);

        $results = [];
        foreach ($rawResults["features"] as $feature) {
            $coordinates = $feature["geometry"]["coordinates"];
            $geocoding = $feature["properties"]["geocoding"];

            $address = new Address();
            $address->lat = $coordinates[1];
            $address->lng = $coordinates[0];
            $address->sourceGeocoder = "nominatim";
            $address->sourceId = $geocoding["place_id"] ?? NULL;
            $address->name = $geocoding["name"] ?? NULL;
            $address->street = $geocoding["street"] ?? NULL;
            $address->housenumber = $geocoding["housenumber"] ?? NULL;
            $address->postcode = $geocoding["postcode"] ?? NULL;
            $address->city = $geocoding["city"] ?? NULL;
            $address->country = strtoupper($geocoding["country"]) ?? NULL;
            $address->category = str_replace("_", " ", ucfirst($geocoding["osm_key"]) ."/".ucfirst($geocoding["osm_value"]));

            $results[] = $address;
        }
        return $results;
    }


    function reverse($f3, $lat, $lng) {
        $db = $this->db;
        $userAgent = $f3->get("SERVER.HTTP_USER_AGENT");

        $url = "https://nominatim.openstreetmap.org/reverse?lat=".$lat."&lon=".$lng."&format=geocodejson";
        $result = $this->getJson($url, $userAgent);

        $address = new Address();

        //$coordinates = $result["features"][0]["geometry"]["coordinates"];
        $geocoding = $result["features"][0]["properties"]["geocoding"];

        $address->lat = $lat;
        $address->lng = $lng;

        $address->sourceGeocoder = "nominatim";
        $address->sourceId = $geocoding["place_id"];

        $address->street = $geocoding["street"] ?? NULL;

        if ($geocoding["type"] == "street") {
            $address->street = $geocoding["name"] ?? NULL;
        }
        $address->housenumber = $geocoding["housenumber"] ?? NULL;
        $address->postcode = $geocoding["postcode"] ?? NULL;
        $address->city = $geocoding["city"] ?? NULL;

        if ($geocoding["city"] && $geocoding["district"]) {
            $address->city = $geocoding["city"].", ".$geocoding["district"];
        }

        $address->country = strtoupper($geocoding["country_code"]) ?? NULL;

        return $address;
    }

    function getJson($url, $userAgent) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
            "Accept-Encoding: gzip, deflate, br",
            "Accept-Language: de,en-US;q=0.7,en;q=0.3",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "DNT: 1",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:124.0) Gecko/20100101 Firefox/124.0"
        ));
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

}
