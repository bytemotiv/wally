<?php

class Share extends Controller {

    function resolve($f3, $args) {
        $key = $args["key"];

        $db = $this->db;
        $share = new DB\SQL\Mapper($db, "sharing");
        $share->load(array("key=?", $key));

        if ($share->dry()) {
            echo "Invalid share link";
        } else {
            $f3->set("SESSION.share", $share->key);
            $f3->reroute("@index", false);
        }
    }

    function release($f3, $args) {
        $f3->set("SESSION.share", NULL);
        $f3->reroute("@index", false);
    }

    function marker($f3, $args) {
        $markerId = $args["marker"];

        $db = $this->db;

        $share = new DB\SQL\Mapper($db, "sharing");
        $share->load(array("type='marker' AND value=?", $markerId));

        if ($share->dry()) {
            $bytes = random_bytes(12);
            $share->key = bin2hex($bytes);
            $share->type = "marker";
            $share->value = $markerId;
            $share->created = time();
            $share->save();
        }

        $protocol = !empty($_SERVER["HTTPS"]) ? "https://" : "http://";
        $hostname = $_SERVER["HTTP_HOST"];
        $port = $_SERVER["SERVER_PORT"] == 80 ? "" : ":".$_SERVER["SERVER_PORT"];

        $link = $protocol.$hostname.$port."/share/".$share->key;

        $f3->set("sharetype", "marker");
        $f3->set("link", $link);
        echo Template::instance()->render("share-dialog.html");
    }


    function category($f3, $args) {
        $categoryId = $args["category"];

        $db = $this->db;

        $share = new DB\SQL\Mapper($db, "sharing");
        $share->load(array("type='category' AND value=?", $categoryId));

        if ($share->dry()) {
            $bytes = random_bytes(12);
            $share->key = bin2hex($bytes);
            $share->type = "category";
            $share->value = $categoryId;
            $share->created = time();
            $share->save();
        }

        $protocol = !empty($_SERVER["HTTPS"]) ? "https://" : "http://";
        $hostname = $_SERVER["HTTP_HOST"];
        $port = $_SERVER["SERVER_PORT"] == 80 ? "" : ":".$_SERVER["SERVER_PORT"];

        $link = $protocol.$hostname.$port."/share/".$share->key;

        $f3->set("sharetype", "category");
        $f3->set("link", $link);
        echo Template::instance()->render("share-dialog.html");
    }

}
