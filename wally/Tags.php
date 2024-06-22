<?php

class Tags extends Controller {

    function datalist($f3, $args) {
        $db = $this->db;
        $tags = new DB\SQL\Mapper($db, "tags");
        $tags = $tags->find();

        $uniqueTags = [];
        foreach ($tags as $tag) {
            if (!in_array($tag->tag, $uniqueTags)) {
                $uniqueTags[] = $tag->tag;
            }
        }

        $entries = "";
        foreach ($uniqueTags as $tag) {
            $entries .= "<option value=\"".$tag."\">";
        }
        echo $entries;
    }

}