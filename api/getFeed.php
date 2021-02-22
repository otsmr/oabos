<?php

if (!$loaded_from_index) die();

require_once "load.php";
require_once "reader/read.php";
require_once "cache/cache.php";

$dbFeed = getFeedFromFile();

$onlyYT = [];
$onlyRss = [];

foreach ($dbFeed as $item) {
    if($item->type === "yt") array_push($onlyYT, $item);
    else if($item->type === "rss") array_push($onlyRss, $item);
}

/*
#######################################
    Config
#######################################
*/
$refresh = false;

$requestID = md5(json_encode($onlyRss) . "<=>". json_encode($onlyYT));

if(isset($_POST["refresh"])){
    $refresh = $_POST["refresh"];
}

/*
#######################################
    Cache
#######################################
*/
$cache = new \Cache\Cache();
$fromCache = false;

if(!$refresh && $data = $cache->load($requestID)){
    $fromCache = true;
    $items = $data;
}else{

    $reader = new \Read\Reader();
    $items = $reader->getFeed($onlyYT, $onlyRss);

    $items = json_decode(json_encode($items));

    $cache->update($requestID, $items);

}