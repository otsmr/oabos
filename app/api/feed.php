<?php

require_once "cache.php";
require_once "db.php";

$db = new DB($odmin->session->user_id);

$yt_abos = $db->get_content();

$refresh = false;
if(isset($_POST["refresh"])) 
    $refresh = true;

$cacheID = md5(json_encode($yt_abos));

$cache = new Cache();

if($refresh || !($feed = $cache->load($cacheID))) {

    $feed = [];

    foreach ($yt_abos as &$yt_abo) {
		
		ini_set('default_socket_timeout', 1);
		
		$options  = array(
			'http' => array(
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:101.0) Gecko/20100101 Firefox/101.0'
			)
		);
		
        $data = @file_get_contents($yt_abo->url, false, $context);


        if (!$data)
            continue;

        $data = str_replace("<media:group>", "<mediagroup>", $data);
        $data = str_replace("</media:group>", "</mediagroup>", $data);
        $data = str_replace("<media:thumbnail", "<mediathumbnail", $data);
        $data = str_replace("<media:description>", "<mediadescription>", $data);
        $data = str_replace("</media:description>", "</mediadescription>", $data);

        $data = simplexml_load_string($data);

        $videos = ((array) $data)["entry"];
		
		if (gettype($videos) == "object") {
			$videos = [$videos];
		}

        foreach ($videos as &$video) {

            array_push($feed, [
                "channel_name" => (string) $yt_abo->name,
                "id" => (string) $video->id,
                "link" => (string) $video->link["href"],
                "title" => (string) $video->title,
                "desc" => "",
                "img" => (string) $video->mediagroup->mediathumbnail->url,
                "date" => (string) $video->published,
            ]);
        }
    
    }

    uasort($feed, function($a, $b) {
        return ($a['date'] > $b['date']) ? -1 : 1;
    });

    $feed = @json_decode(json_encode($feed));

    $cache->update($cacheID, $feed);

}