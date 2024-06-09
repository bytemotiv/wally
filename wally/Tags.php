<?php

class Tags extends Controller {

    function datalist($f3, $args) {
        $db = $this->db;
        $tags = new DB\SQL\Mapper($db, "tags");
        $tags = $tags->find();

        $entries = "";
        foreach ($tags as $tag) {
            $entries .= "<option value=\"".$tag->tag."\">";
        }
        echo $entries;
    }

}