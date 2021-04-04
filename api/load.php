<?php

require_once "api/odmin/init.php";
require_once "utils.php";

$logged = false;
$apikey = false;

if (isset($_GET["apikey"])) {
    $apikey = preg_replace('/([^A-Za-z0-9_])/g', "", (string) $_GET["apikey"]);
}

$odmin->init_session_from_cookie();

$root = __DIR__ . "/../feeds/";

if ($apikey && is_dir($root . $apikey)) {

    $root = $root . $apikey;    

} else if ($odmin->is_logged_in()) {

    $folder_name = "";

    $d = dir($root);

    while (false !== ($entry = $d->read()))
    {
        if (is_dir($root . $entry) && (startsWith($entry, $odmin->session->user_id . "_"))) {
            $folder_name = $entry;
            break;
        }
    }

    if ($folder_name === "") {
        $folder_name = $odmin->session->user_id . "_" . getRandString();
    }

    $root = $root . $folder_name;

    if(!is_dir($root))
        mkdir($root, 0700);

} else {
    header("Location: " . $odmin->get_signin_url());
    die();
}

$path = $root . "/feed.json";