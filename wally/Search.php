<?php

class Search extends Controller {

    function search($f3, $args) {
        $db = $this->db;
        $query = $f3->get("GET.query");
        //$query = empty($args["query"]) ? NULL : $args["query"];

        $geocoder = new GeocoderNominatim;
        $results = $geocoder->search($f3, $query);

        $f3->set("results", $results);
        echo Template::instance()->render("search-results.html");
    }

    function getJson($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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

