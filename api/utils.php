<?php 

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

function getRandString ($l = 30) {
    return getRandID($l, "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890");
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

function str_starts_with( $haystack, $needle ) {
    $length = strlen( $needle );
    return substr( $haystack, 0, $length ) === $needle;
}