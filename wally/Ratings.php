<?php

class Ratings extends Controller {

    function list($f3, $args) {
        echo Template::instance()->render("ratings-list.html");
    }

}
