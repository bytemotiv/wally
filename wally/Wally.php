<?php

class Wally extends Controller {

    function index($f3, $args) {
        $db = $this->db;
        $category = new DB\SQL\Mapper($db, "categories");
        $categories = $category->find();
        $f3->set("categories", $categories);

        $marker = new DB\SQL\Mapper($db, "markers");
        $marker->categoryName = "SELECT name FROM categories WHERE categories.id = markers.category";
        $marker->ratingName = "SELECT name FROM ratings WHERE ratings.id = markers.rating";
        $markers = $marker->find();
        $f3->set("markers", $markers);

        echo Template::instance()->render("base.html");
    }


    function login($f3, $args) {
        if ($f3->get("POST")) {
            $password = $f3->GET("POST.password");
            if ($password == $_ENV["ADMIN_PASSWORD"]) {
                $now = time();
                $f3->set("SESSION.login", $now);
                $f3->reroute("@index", false);
            }
        }
        echo Template::instance()->render("login.html");
    }


    function logout($f3, $args) {
        $f3->set("SESSION.login", NULL);
        $f3->reroute("@login", false);
    }


    function setup($f3, $args) {
        $db = $this->db;

        if ($f3->get("POST")) {
            $env = [];
            foreach ($_POST as $key=>$value) {
                $env[$key] = $value;
            }
            $this->writeEnvFile(".env", $env);
            $db->exec(explode(";", $f3->read("wally/setup.sql")));

            $f3->reroute("@login", false);
        }

        echo Template::instance()->render("setup.html");

    }

    // --- Helpers for modifying the .env file ------------------------------------------------------------------------

    function writeEnvFile($filePath, $envArray) {
        $lines = [];

        foreach ($envArray as $key => $value) {
            // Add quotes if the value contains spaces or special characters
            if (preg_match("/\s/", $value) || preg_match('/[\'"=]/', $value)) {
                $value = "\"" . addslashes($value) . "\"";
            }
            $lines[] = "$key=$value";
        }

        $content = implode(PHP_EOL, $lines) . PHP_EOL;
        file_put_contents($filePath, $content);
    }

}

