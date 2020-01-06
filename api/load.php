<?php
$logged = false;
$apikey = false;

if (isset($_GET["apikey"])) {
	
    $apikey = $_GET["apikey"];
	
}

session_name('sid');
session_start();

require_once __DIR__ . "/../config.php";

if(isset($_COOKIE['token'])){
    
    try {

        $res = json_decode(file_get_contents($apiURL));
    
        if(isset($res->valid) && $res->valid) {
            $userID = $res->user->id;
            $logged = true;
        }

    } catch (\Throwable $th) { }

}

if (!$logged && !$apikey) {
    header("Location: $loginURL");
    die();
}

if (!$apikey) {
    $uniqueID = md5($CONFIG["dbsecret"] . $userID . $CONFIG["dbsecret"]);
} else {
    $uniqueID = $apikey;
}

$root = __DIR__ . "/../feeds/" . $uniqueID;
if(!is_dir($root)) mkdir($root, 0700);

$path = $root . "/feed.json";


function updateFeedFile ($data) {
    global $path;
    $handle = fopen($path, "w");
	$write = @fwrite ($handle, json_encode($data));
	fclose ($handle);
}

function getFeedFromFile () {
    global $path;
    if (!is_file($path)) return [];
    return json_decode(file_get_contents($path));
}

function getRandID($l = 9, $c = "1234567890", $u = FALSE) {
    for ($s = '', $i = 0, $z = strlen($c)-1; $i < $l; $x = rand(0,$z), $s .= $c{$x}, $i++);
    return $s;
}

function startsWith($haystack, $needle){
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function error ($code) {
    die(json_encode([
        "error" => $code
    ]));
}

function success($code = true){
    die(json_encode([
        "ok" => $code
    ]));
}