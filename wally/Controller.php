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
        //$db=$this->db;
        // Prepare user menu
        //$f3->set("menu", $db->exec('SELECT slug,title FROM pages ORDER BY position;'));
    }

    // HTTP route post-processor
    function afterroute() {
        //echo Template::instance()->render("base.html");
    }

    // Instantiate class
    function __construct() {
        $f3=Base::instance();

        $f3->set("CACHE", true);

        $f3->set("loggedin", false);

        // create database if needed
        $db = new DB\SQL($f3->get("db"));

        if (file_exists("db/setup.sql")) {
            $db->exec(explode(";", $f3->read("db/setup.sql")));
            rename("db/setup.sql", "db/setup.sql_done");
        }

        // store DB variable
        new DB\SQL\Session($db);
        $this->db=$db;

        \Preview::instance()->filter("nl2br","\Helper::instance()->nl2br");

        \Template::instance()->extend("css","AssetHelper::importCSS");
        \Template::instance()->extend("js","AssetHelper::importJS");

    }

}