<?php 

$ytid = htmlentities($_GET["id"]);
$ytid = preg_replace("/[^a-zA-Z0-9-_]+/", "", $ytid);

if (strlen($ytid) > 15) die();

$url = "https://img.youtube.com/vi/$ytid/mqdefault.jpg";

$imginfo = getimagesize( $url );

if ($imginfo['mime'] !== "image/jpeg") {

    $imginfo = getimagesize( "./placeholder.png" );
    header("Content-type: " . $imginfo['mime']);
    readfile( "./placeholder.png" );

} else {
    
    header("Content-type: " . $imginfo['mime']);
    readfile( $url );
}