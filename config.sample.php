<?php

$CONFIG = array (
  'dbsecret' => '',
  'odmin_service_name' => '',
  'odmin_base_url' => ""
);

$loginURL = $CONFIG["odmin_base_url"] . "/login?service=" . $CONFIG["odmin_service_name"];
$apiURL = $CONFIG["odmin_base_url"] . "/api/istokenvalid/" . $_COOKIE['token'];