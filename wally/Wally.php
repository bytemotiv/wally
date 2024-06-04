<?php

class Wally extends Controller {

    function index($f3, $args) {
        $f3->set("CACHE",FALSE);

        $db = $this->db;
        $category = new DB\SQL\Mapper($db, "categories");
        $categories = $category->find();
        $f3->set("categories", $categories);

        $marker = new DB\SQL\Mapper($db, "markers");
        $marker->categoryName = "SELECT name FROM categories WHERE categories.id = markers.category";
        $marker->ratingName = "SELECT name FROM ratings WHERE ratings.id = markers.rating";
        $markers = $marker->find();
        $f3->set("markers", $markers);

        /*
        $tokenCookie = $f3->get("COOKIE.token_login", NULL);
        $tokenArgs = empty($args["token"]) ? NULL : $args["token"];

        if ($tokenArgs) {
            setcookie("token_login", $tokenArgs, time() + 365 * 24 * 60 * 60, "/");
            $f3->reroute('@index');
        }

        if ($tokenCookie) {
            $db = $this->db;
            $user = new DB\SQL\Mapper($db,"user");
            $user->load(array("token_login=?", $tokenCookie));

            if ($user->dry()) {
                setcookie("token_login", "", -1);
                $f3->set("errormessage", "Your saved credentials don't seem to be valid anymore. Ask the person who invited you to send you a new login link");
                echo Template::instance()->render("base-notloggedin.html");
                return;
            } else {
                setcookie("user_id", $user["id"], time() + 365 * 24 * 60 * 60, "/");
            }
        } else {
            $f3->set("errormessage", "No saved credentials could be found. Ask for a login link.");
            echo Template::instance()->render("base-notloggedin.html");
            return;
        }

        // --- proceed with loading the map view
        */

        echo Template::instance()->render("base.html");
    }

}

