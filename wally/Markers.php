<?php

class Markers extends Controller {

    function geojson($f3, $args) {
        $db = $this->db;

        $marker = new DB\SQL\Mapper($db, "markers");
        $markers = [];

        $sessionShare = $f3->get("SESSION.share");
        if ($sessionShare == NULL) {
            $markers = $marker->find();
        } else {
            $share = new DB\SQL\Mapper($db, "sharing");
            $share->load(array("key=?", $sessionShare));

            if ($share->type == "marker") {
                $markers = $marker->find(array("id=?", $share->value));
            }

            if ($share->type == "category") {
                $markers = $marker->find(array("category=?", $share->value));
            }

            if ($share->type == "tag") {
                $marker = new DB\SQL\Mapper($db, "markersWithTags");
                $markers = $marker->find(array("tag=?", $share->value));
            }

        }


        $category = new DB\SQL\Mapper($db, "categories");
        $categories = [];
        $categories[0] = [
            "color" => "#515151",
            "icon" => "map-pin",
        ];

        foreach ($category->find() as $cat) {
            $categories[$cat->id] = [
                "color" => $cat->color,
                "icon" => $cat->icon,
                "name" => $cat->name,
            ];
        }

        $features = [];
        foreach ($markers as $marker) {
            $tags = new DB\SQL\Mapper($db, "tags");
            $tags = $tags->find(array("marker=?", $marker->id));
            $taglist = [];
            foreach ($tags as $t) {
                $taglist[] = $t->tag;
            }

            $feature = [
                "type" => "Feature",
                "properties" => [
                    "id" => $marker->id,
                    "category" => $marker->category,
                    "rating" => $marker->rating,
                    "title" => $marker->name,
                    "color" => $categories[$marker->category]["color"],
                    "icon" => $categories[$marker->category]["icon"],
                    "tags" => $taglist,
                    "categoryValue" => $categories[$marker->category]["name"],
                    "ratingValue" => "Rating: ".$marker->rating,
                ],
                "geometry" => [
                    "type" => "Point",
                    "coordinates" => [
                        floatval($marker->lng),
                        floatval($marker->lat)
                    ]
                ]
            ];
            $features[] = $feature;
        }

        $geoJson = [
            "type" => "FeatureCollection",
            "features" => $features,
        ];

        header("Content-Type: application/json");
        echo json_encode($geoJson);
    }

    function category($f3, $args) {
        $id = $args["id"];

        $db = $this->db;
        $marker = new DB\SQL\Mapper($db,"markers");
        $marker = $marker->load(array("id=?", $id));

        if ($f3->get("BODY")) {
            $category = $f3->get("POST.category");
            $marker->category = $category;
            $marker->save();

            // remove empty categories
            $db->exec("DELETE FROM categories WHERE id NOT IN (SELECT DISTINCT category FROM markers)");

            return $this->details($f3, $args);
        } else {
            $category = new DB\SQL\Mapper($db, "categories");
            $category->amount = "SELECT COUNT(*) FROM markers WHERE markers.category == categories.id";
            $categories = $category->find();
            $f3->set("categories", $categories);

            $f3->set("marker", $marker);

            echo Template::instance()->render("marker-categories.html");
        }
    }

    function delete($f3, $args) {
        $id = $args["id"];

        $db = $this->db;
        $marker = new DB\SQL\Mapper($db,"markers");
        $marker = $marker->load(array("id=?", $id));
        if (!$marker->dry()) {
            $marker->erase();
        }
    }

    function rating($f3, $args) {
        $id = $args["id"];

        $db = $this->db;
        $marker = new DB\SQL\Mapper($db,"markers");
        $marker = $marker->load(array("id=?", $id));

        if ($f3->get("BODY")) {
            $rating = $f3->get("POST.rating");
            $marker->rating = $rating;
            $marker->save();

            return $this->details($f3, $args);
        } else {
            //$rating = new DB\SQL\Mapper($db, "categories");
            //$categories = $rating->find();
            $f3->set("marker", $marker);
            echo Template::instance()->render("marker-rating.html");
        }
    }

    function create($f3, $args) {
        $name = $f3->get("POST.name") ?? "Unnamed marker";
        $lat = floatval($f3->get("POST.lat") ?? 0);
        $lng = floatval($f3->get("POST.lng") ?? 0);
        $category = intval($f3->get("POST.category") ?? 0);
        $rating = intval($f3->get("POST.rating") ?? 0);
        $address = $f3->get("POST.address") ?? NULL;
        $sourceGeocoder = $f3->get("POST.sourceGeocoder") ?? NULL;
        $sourceId = $f3->get("POST.sourceId") ?? NULL;

        $db = $this->db;
        $marker = new DB\SQL\Mapper($db,"markers");

        $geocoder = new $_ENV["GEOCODER"];
        $result = $geocoder->reverse($f3, $lat, $lng);

        $marker->lat = $lat;
        $marker->lng = $lng;
        $marker->category = $category;
        $marker->rating = $rating;

        if ($address == NULL) {
            $marker->name = $result->name ?? $name;
            $marker->address = $result->getAddress();
            $marker->sourceGeocoder = $result->sourceGeocoder;
            $marker->sourceId = $result->sourceId;
        } else {
            $marker->name = $name;
            $marker->address = $address;
            $marker->sourceGeocoder = $sourceGeocoder;
            $marker->sourceId = $sourceId;
        }

        $marker->save();

        return $this->details($f3, array("id" => $marker->id));
    }

    function details($f3, $args) {
        $db = $this->db;

        $marker = new DB\SQL\Mapper($db, "markers");
        $marker = $marker->load(array("id=?", $args["id"]));
        $marker->lat = round($marker->lat, 4);
        $marker->lng = round($marker->lng, 4);
        $f3->set("marker", $marker);

        $tag = new DB\SQL\Mapper($db, "tags");
        $tag->amount = 1;
        $tags = $tag->find(array("marker=?", $marker->id), array("order" => "tag"));
        $f3->set("tags", $tags);

        $category = new DB\SQL\Mapper($db, "categories");
        $category->load(array("id=?", $marker->category));

        if ($category->dry()) {
            $category = new stdClass();
            $category->name = "No Category";
            $category->color = "#515151";
            $category->icon = "map-pin";
        }
        $f3->set("category", $category);

        $ratings = [
            0 => [
                "icon" => "smiley-blank",
                "name" => "No Rating"
            ],
            1 => [
                "icon" => "clipboard-text",
                "name" => "Wishlist"
            ],
            4 => [
                "icon" => "thumbs-up",
                "name" => "Good"
            ],
            5 => [
                "icon" => "fire",
                "name" => "Excellent"
            ]
        ];

        $rating = new stdClass();
        $rating->id = $marker->rating;
        if ($marker->rating) {
            $rating->name = $ratings[$marker->rating]["name"];
            $rating->icon = $ratings[$marker->rating]["icon"];
        } else {
            $rating->name = "No Rating";
            $rating->icon = "smiley-blank";
        }
        $f3->set("rating", $rating);

        $upload = new DB\SQL\Mapper($db, "uploads");
        $uploads = $upload->find(array("marker=?", $marker->id), array("order" => "date"));
        $f3->set("uploads", $uploads);

        echo Template::instance()->render("marker-details.html");
    }

    function edit($f3, $args) {
        $db = $this->db;

        // POST
        if ($f3->get("BODY")) {
            $data = json_decode($f3->GET("POST.data"));

            $marker = new DB\SQL\Mapper($db, "markers");
            $marker = $marker->load(array("id=?", $args["id"]));

            $marker->name = $data->name;
            $marker->notes = $data->notes;
            $marker->save();

            // write new tags
            $tag = new DB\SQL\Mapper($db, "tags");
            $tags = $tag->find(array("marker=?", $marker->id));
            foreach ($tags as $tag) {
                $tag->erase();
            }

            foreach ($data->tags as $tag) {
                $newTag = new DB\SQL\Mapper($db, "tags");
                $newTag->marker = $marker->id;
                $newTag->tag = $tag;
                $newTag->save();
            }

            // remove deleted pictures
            $upload = new DB\SQL\Mapper($db, "uploads");
            $uploads = $upload->find(array("marker=?", $marker->id));
            foreach ($uploads as $u) {
                $keep = false;
                foreach ($data->photos as $photo) {
                    if ($u->image == $photo) {
                        $keep = true;
                    }
                }
                if (!$keep) {
                    $u->erase();
                    unlink($u->image);
                    unlink($u->thumb);
                    // TODO: Security check for paths
                }
            }


            return $this->details($f3, $args);
        }

        $marker = new DB\SQL\Mapper($db, "markers");
        $marker = $marker->load(array("id=?", $args["id"]));
        $marker->lat = round($marker->lat, 4);
        $marker->lng = round($marker->lng, 4);
        $f3->set("marker", $marker);

        $tag = new DB\SQL\Mapper($db, "tags");
        $tag->amount = 1;
        $tags = $tag->find(array("marker=?", $marker->id), array("order" => "tag"));
        $f3->set("tags", $tags);

        $category = new DB\SQL\Mapper($db, "categories");
        $category->load(array("id=?", $marker->category));

        if ($category->dry()) {
            $category = new stdClass();
            $category->name = "No Category";
            $category->color = "#515151";
            $category->icon = "map-pin";
        }
        $f3->set("category", $category);

        $ratings = [
            0 => [
                "icon" => "smiley-blank",
                "name" => "No Rating"
            ],
            1 => [
                "icon" => "clipboard-text",
                "name" => "Wishlist"
            ],
            4 => [
                "icon" => "thumbs-up",
                "name" => "Good"
            ],
            5 => [
                "icon" => "fire",
                "name" => "Excellent"
            ]
        ];

        $rating = new stdClass();
        $rating->id = $marker->rating;
        $rating->name = $ratings[$marker->rating]["name"];
        $rating->icon = $ratings[$marker->rating]["icon"];
        $f3->set("rating", $rating);

        $upload = new DB\SQL\Mapper($db, "uploads");
        $uploads = $upload->find(array("marker=?", $marker->id), array("order" => "date"));
        $f3->set("uploads", $uploads);

        echo Template::instance()->render("marker-edit.html");
    }

    function upload($f3, $args) {
        $db = $this->db;

        $marker = new DB\SQL\Mapper($db, "markers");
        $marker = $marker->load(array("id=?", $args["id"]));

        $files = $f3->get("FILES");
        $file = $files["file"];

        $extension = "jpg";
        $uuid = "uploads/".uniqid($marker->id."-");
        $name = $uuid.".".$extension;
        $nameThumb = $uuid."-thumbnail.".$extension;

        move_uploaded_file($file["tmp_name"], $name);

        $img = new Image($name);
        $img->resize(150, 150, true, true);
        $f3->write($nameThumb, $img->dump("jpeg", 90));

        $upload = new DB\SQL\Mapper($db, "uploads");
        $upload->marker = $marker->id;
        $upload->image = $name;
        $upload->thumb = $nameThumb;
        $upload->date = date(DATE_ATOM);
        $upload->save();

        return $this->details($f3, $args);
    }
}
