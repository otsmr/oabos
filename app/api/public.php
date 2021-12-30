<?php

require_once __DIR__ . "/odmin/init.php";
require_once __DIR__ . "/db.php";

$odmin->init_session_from_cookie();

if (!$odmin->is_logged_in() || !isset($_POST["type"])) die();

$db = new DB($odmin->session->user_id);

function getRandID($l = 9, $c = "1234567890", $u = FALSE) {
    for ($s = '', $i = 0, $z = strlen($c)-1; $i < $l; $x = rand(0,$z), $s .= $c[$x], $i++);
    return $s;
}

switch ((string) $_POST["type"]) {

    case "remove":

        $data = $db->get_content();

        $new = [];

        foreach ($data as $key => $item) {
            if($item->id != (string) $_POST["id"])
                array_push($new, $item);
        }

        $db->update_content($new);

        break;

    case "add":

        $name = htmlentities((string) $_POST["name"]);
        $channel_id = htmlentities((string) $_POST["id"]);

        $url = "https://www.youtube.com/feeds/videos.xml?channel_id=$channel_id";

        $content = @file_get_contents($url);

        if (!$content) die(json_encode([
            "error" => "Channel not found."
        ]));

        $data = $db->get_content();

        array_push($data, [
            "id" => getRandID(15),
            "name" => $name,
            "url" => $url
        ]);

        $db->update_content($data);

        break;
    
    default:
        break;
}


die(json_encode([
    "ok" => "sucess"
]));