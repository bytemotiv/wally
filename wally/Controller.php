<?php

class Helper extends \Prefab {
    function nl2br($val) {
        return nl2br($val);
    }
}

// TODO: https://fatfreeframework.com/3.8/optimization#keeping-javascript-and-css-on-a-healthy-diet
class AssetHelper {
    static public function importCSS($args) {
        $attr = $args["@attrib"];
        $src = $attr["src"];

        $file = __DIR__ ."/../".$src;

        if (file_exists($file)) {
            $date = filemtime($file);
            return "<link rel='stylesheet' type='text/css' href='".$src."?v=".$date."'>";
        } else {
            return "<!-- CSS not found: ".$file."-->";
        }
    }

    static public function importJS($args) {
        $attr = $args["@attrib"];
        $src = $attr["src"];

        $file = __DIR__ ."/../".$src;

        if (file_exists($file)) {
            $date = filemtime($file);
            return "<script type='text/javascript' src='".$src."?v=".$date."'></script>";
        } else {
            return "<!-- JS not found: ".$file."-->";
        }
    }

}


class Controller {

    protected $db;

    // HTTP route pre-processor
    function beforeroute($f3) {
        // check for either login or a share token if accessing a page other than the login or the share view
        $db = $this->db;
        new \DB\SQL\Session($db);

        $requestPath = $f3->get("PARAMS.0");
        if (!str_starts_with($requestPath, "/share") && !str_starts_with($requestPath, "/login") && !str_starts_with($requestPath, "/logout")) {
            $login = $f3->get("SESSION.login");
            $share = $f3->get("SESSION.share");

            $f3->set("LOGIN", false);
            $f3->set("SHARE", false);

            if ($login == NULL) {
                if ($share == NULL) {
                    $f3->reroute("@login", false);
                } else {
                    $db = $this->db;
                    $shareDetails = new DB\SQL\Mapper($db, "sharing");
                    $shareDetails->load(array("key=?", $share));
                    $f3->set("SHARE", $shareDetails);

                    $f3->set("SHARE_title", "Sharing ".$shareDetails->key);

                    if ($shareDetails->type == "category") {
                        $category = new DB\SQL\Mapper($db, "categories");
                        $category->load(array("id=?", $shareDetails->value));
                        $shareTitle = "Markers in the category <b>".$category->name."</b>";
                        $f3->set("SHARE_category", $category);
                        $f3->set("SHARE_title", $shareTitle);
                    }
                }
            } else {
                $f3->set("LOGIN", $login);
            }
        }
    }

    // HTTP route post-processor
    function afterroute() {
    }

    // Instantiate class
    function __construct() {
        $f3=Base::instance();
        $f3->set("CACHE", true);

        // create database if needed
        $db = new DB\SQL($f3->get("db"));

        if (file_exists("db/setup.sql")) {
            $db->exec(explode(";", $f3->read("db/setup.sql")));
            rename("db/setup.sql", "db/setup.sql_done");
        }

        // store DB variable
        $this->db=$db;
        //new \DB\SQL\Session($db);

        \Preview::instance()->filter("nl2br","\Helper::instance()->nl2br");

        \Template::instance()->extend("css","AssetHelper::importCSS");
        \Template::instance()->extend("js","AssetHelper::importJS");

    }

}