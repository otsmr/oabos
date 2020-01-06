<?php

namespace API;

if(!isset($_COOKIE["token"])) die(json_encode([
    "error" => "Kein User angemeldet"
]));

if(!isset($_POST["type"])) die(json_encode([
    "error" => "Fehlerhafte Anfrage"
]));

require_once __DIR__ . "/load.php";


if ($_POST["type"] === "removeItem") {

    $data = getFeedFromFile();

    $new = [];

    foreach ($data as $key => $item) {
        if($item->id != $_POST["id"]){
            array_push($new, $item);
        }
    }

    updateFeedFile($new);

    success();

}


if ($_POST["type"] === "addFeed") {

    $name = $_POST["name"];
    $feed = $_POST["id"];

    if(startsWith($feed, "http")){
        $type = "rss";
    }else{

        $site = "https://www.youtube.com/feeds/videos.xml?channel_id=$feed";
        $content = @file_get_contents($site);

        if (!$content) {

            $site = "https://www.youtube.com/feeds/videos.xml?user=$feed";
            $content = @file_get_contents($site);

            if (!$content) error($name);
            
        }
        $feed = $site;
        $type = "rss";
    }

    $data = getFeedFromFile();

    array_push($data, [
        "id" => getRandID(15),
        "name" => $name,
        "src" => $feed,
        "type" => $type
    ]);

    updateFeedFile($data);

    success();

}