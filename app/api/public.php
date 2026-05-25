<?php

require_once __DIR__ . "/auth.php";
$odmin = new \ODMIN\OAuth();
require_once __DIR__ . "/db.php";

$odmin->init_session_from_cookie();

if (!$odmin->is_logged_in() || !isset($_POST["type"])) die();

$db = new DB($odmin->session->user_id);

function getRandID($l = 9, $c = "1234567890", $u = FALSE) {
    for ($s = '', $i = 0, $z = strlen($c)-1; $i < $l; $x = rand(0,$z), $s .= $c[$x], $i++);
    return $s;
}

function fetch_channel_details_from_feed($channel_id) {
    $feed_url = "https://www.youtube.com/feeds/videos.xml?channel_id=$channel_id";
    $options = [
        'http' => [
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:101.0) Gecko/20100101 Firefox/101.0',
            'timeout' => 5
        ]
    ];
    $context = stream_context_create($options);
    $feed_content = @file_get_contents($feed_url, false, $context);
    if (!$feed_content) {
        return [
            'id' => $channel_id,
            'name' => 'Kanal ' . substr($channel_id, 0, 6)
        ];
    }
    
    $channel_name = '';
    if (preg_match('/<title>([^<]+)<\/title>/i', $feed_content, $matches)) {
        $channel_name = html_entity_decode($matches[1]);
    }
    
    return [
        'id' => $channel_id,
        'name' => !empty($channel_name) ? trim($channel_name) : 'Kanal ' . substr($channel_id, 0, 6)
    ];
}

function resolve_youtube_channel($url) {
    $url = trim($url);
    
    // 1. Check if we can parse the channel ID directly from the URL to avoid loading HTML
    if (preg_match('/^(UC[a-zA-Z0-9_-]{22})$/', $url, $matches)) {
        return fetch_channel_details_from_feed($matches[1]);
    }
    if (preg_match('/youtube\.com\/channel\/(UC[a-zA-Z0-9_-]{22})/i', $url, $matches)) {
        return fetch_channel_details_from_feed($matches[1]);
    }
    if (preg_match('/youtube\.com\/feeds\/videos\.xml\?channel_id=(UC[a-zA-Z0-9_-]{22})/i', $url, $matches)) {
        return fetch_channel_details_from_feed($matches[1]);
    }
    
    // 2. Fetch the URL content
    $options = [
        'http' => [
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:101.0) Gecko/20100101 Firefox/101.0',
            'timeout' => 5
        ]
    ];
    $context = stream_context_create($options);
    $html = @file_get_contents($url, false, $context);
    if (!$html) {
        return null;
    }
    
    // 3. Extract Channel ID from HTML
    $channel_id = null;
    if (preg_match('/<link rel="canonical" href="https:\/\/www\.youtube\.com\/channel\/(UC[a-zA-Z0-9_-]{22})"/i', $html, $matches)) {
        $channel_id = $matches[1];
    }
    if (!$channel_id && preg_match('/<meta property="og:url" content="https:\/\/www\.youtube\.com\/channel\/(UC[a-zA-Z0-9_-]{22})"/i', $html, $matches)) {
        $channel_id = $matches[1];
    }
    if (!$channel_id && preg_match('/youtube\.com\/feeds\/videos\.xml\?channel_id=(UC[a-zA-Z0-9_-]{22})/', $html, $matches)) {
        $channel_id = $matches[1];
    }
    if (!$channel_id && preg_match('/"externalChannelId"\s*:\s*"(UC[a-zA-Z0-9_-]{22})"/i', $html, $matches)) {
        $channel_id = $matches[1];
    }
    if (!$channel_id && preg_match('/"channelId"\s*:\s*"(UC[a-zA-Z0-9_-]{22})"/i', $html, $matches)) {
        $channel_id = $matches[1];
    }
    if (!$channel_id && preg_match('/itemprop="channelId"\s+content="(UC[a-zA-Z0-9_-]{22})"/i', $html, $matches)) {
        $channel_id = $matches[1];
    }
    
    if (!$channel_id) {
        return null;
    }
    
    // 4. Extract Channel Name from HTML
    $channel_name = "";
    if (preg_match('/<link itemprop="name" content="([^"]+)"/i', $html, $matches)) {
        $channel_name = html_entity_decode($matches[1]);
    }
    
    if (empty($channel_name)) {
        return fetch_channel_details_from_feed($channel_id);
    }
    
    return [
        'id' => $channel_id,
        'name' => trim($channel_name)
    ];
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

        if (isset($_POST["url"])) {
            $youtube_url = (string) $_POST["url"];
            $resolved = resolve_youtube_channel($youtube_url);
            
            if (!$resolved) {
                die(json_encode([
                    "error" => "Kanal oder Video konnte nicht gefunden werden. Bitte überprüfe die URL."
                ]));
            }
            
            $name = $resolved['name'];
            $channel_id = $resolved['id'];
        } else {
            $name = htmlentities((string) $_POST["name"]);
            $channel_id = htmlentities((string) $_POST["id"]);
        }

        $url = "https://www.youtube.com/feeds/videos.xml?channel_id=$channel_id";

        $options = [
            'http' => [
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:101.0) Gecko/20100101 Firefox/101.0',
                'timeout' => 5
            ]
        ];
        $context = stream_context_create($options);
        $content = @file_get_contents($url, false, $context);

        if (!$content) die(json_encode([
            "error" => "Kanal-Feed nicht gefunden."
        ]));

        $data = $db->get_content();

        foreach ($data as $item) {
            if ($item->url === $url) {
                die(json_encode([
                    "error" => "Dieser Kanal ist bereits abonniert."
                ]));
            }
        }

        array_push($data, [
            "id" => getRandID(15),
            "name" => $name,
            "url" => $url
        ]);

        $db->update_content($data);

        break;

    case "save_progress":
        if (isset($_POST["video_id"]) && isset($_POST["time"]) && isset($_POST["duration"]) && isset($_POST["percentage"])) {
            $video_id = (string) $_POST["video_id"];
            $time = (float) $_POST["time"];
            $duration = (float) $_POST["duration"];
            $percentage = (float) $_POST["percentage"];
            
            $db->save_watch_progress($video_id, $time, $duration, $percentage);
            die(json_encode(["ok" => true]));
        }
        die(json_encode(["error" => "Fehlende Parameter"]));
        break;

    case "get_progress":
        $progress = $db->get_watch_progress();
        die(json_encode($progress));
        break;
    
    default:
        break;
}

die(json_encode([
    "ok" => "sucess"
]));