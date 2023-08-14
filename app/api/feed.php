<?php

require_once "cache.php";
require_once "db.php";

$refresh = isset($_POST["refresh"]);

$db = new DB($logged_in_odmin_id);

$yt_abos = $db->get_content();
$cacheID = md5(json_encode($yt_abos));

$cache = new Cache();

if ($refresh || !($feed = $cache->load($cacheID))) {

  $feed = [];

  foreach ($yt_abos as &$yt_abo) {

		ini_set('default_socket_timeout', 1);

		$options  = array(
			'http' => array(
				'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:101.0) Gecko/20100101 Firefox/101.0'
			)
		);

    $data = @file_get_contents($yt_abo->url, false, $context);

    if (!$data) {
      continue;
    }

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
      $video_id = substr(strrchr((string) $video->link["href"], "="), 1);
      array_push($feed, [
        "id" => $video_id,
        "channel" => trim((string) $yt_abo->name),
        "title" => (string) $video->title,
        "link" => (string) $video->link["href"],
        "img" => "https://oabos.de/img/youtube.php?id=$video_id",
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
