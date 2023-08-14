<?php

if (!isset($_GET["api_key"]) || !isset($_GET["user_id"])) {
  die("Nop");
}

$api_key = (string) $_GET["api_key"];
$user_id = strval((int) $_GET["user_id"]);

require_once "api/db.php";

$db = new DB($user_id);

if ($db->get_api_key() != $api_key) {
  die("Nö");
}

$logged_in_odmin_id = $user_id;
require_once "api/feed.php";

header('Content-Type: application/json');
echo json_encode($feed);

