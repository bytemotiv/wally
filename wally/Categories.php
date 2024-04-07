<?php

class Categories extends Controller {

    // gives out a list of all existing categories
    function list($f3, $args) {
        $db = $this->db;

        $category = new DB\SQL\Mapper($db, "categories");
        $category->amount = "SELECT COUNT(*) FROM markers WHERE markers.category == categories.id";
        $categories = $category->find();
        $f3->set("categories", $categories);

        $marker = new DB\SQL\Mapper($db, "markers");
        $markers = sizeof($marker->find());
        $f3->set("markers", $markers);

        $markersWithoutCategory = sizeof($marker->find(array("category=?", "0")));
        $f3->set("markersWithoutCategory", $markersWithoutCategory);

        echo Template::instance()->render("categories-list.html");
    }

    // edits a category or creates a new one
    function edit($f3, $args) {
        $id = $args["id"];

        $marker = $args["marker"] ?? NULL;
        $f3->set("marker", $marker);

        $db = $this->db;
        $category = new DB\SQL\Mapper($db,"categories");
        $category->load(array("id=?", $id));

        if ($f3->get("BODY")) {
            $data = json_decode($f3->GET("POST.category"));
            $category->name = $data->name;
            $category->color = $data->color;
            $category->icon = $data->icon;
            $category->save();

            $markerId = $f3->GET("POST.marker") ?? NULL;
            if ($markerId) {
                $markerId = intval($markerId);
                $marker = new DB\SQL\Mapper($db, "markers");
                $marker->load(array("id=?", $markerId));
                $marker->category = $category->id;
                $marker->save();

                $markerController = new Markers();
                return $markerController->details($f3, array("id" => $markerId));
            }

            return $this->list($f3, $args);
        } else {
            if ($category->dry()) {
                $category = new stdClass();
                $category->id = null;
                $category->name = "";
                $category->color = "";
                $category->icon = "";
            }
            $f3->set("category", $category);
            echo Template::instance()->render("category-edit.html");
        }

    }

}
