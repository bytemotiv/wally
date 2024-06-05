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
            if ($password == "test") {
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

}

