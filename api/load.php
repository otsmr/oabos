<?php

/**
 * Session wird initialisiert
 */

session_name('sid');
session_start();


/**
 * Startup
 */

require_once(__DIR__ . "/../config.php");

function odmin () {
    global $CONFIG;

    if (!isset($_COOKIE['token'])) return null;

    $url = $CONFIG["odmin_base_url"] . "/api/istokenvalid/" . $_COOKIE['token'];

    try {

        $res = json_decode(file_get_contents($url));
    
        if(isset($res->valid) && $res->valid) {
            return $res->user->id;
        }

    } catch (\Throwable $th) { }

    return null;

}

class Output{
    
    public function error($code = false, $return = false){
        $r = json_encode([
            "error" => $code
        ]);
        if($return) return $r;
        
        echo $r;
        die();
    }

    public function success($code = true, $return = false){
        $r = json_encode([
            "ok" => $code
        ]);
        if($return) return $r;
        
        echo $r;
        die();
    }

}


class DB{

    private $conn;
    private $o;

    public function __construct(){

        $this->o = new Output();
        $this->connect();

    }

    private function connect(){

        global $CONFIG;
        $this->conn = @mysqli_connect($CONFIG["dbhost"], $CONFIG["dbuser"], $CONFIG["dbpassword"], $CONFIG["dbname"]);

        if(!$this->conn){ 
            $this->o->error("Es konnte keine Verbindung zur Datenbank hergestellt werden.");
        }

    }

    public function get($sql){
        try{
            $res = $this->query($sql);
            if($res) return @mysqli_fetch_array($res);
            else return false;
        }catch(Exception $e){
            return false;
        }
    }

    public function set($sql){
        return @mysqli_query($this->conn, $sql);
    }
    public function query($sql){
        return mysqli_query($this->conn, $sql);
    }

    public function check($check){
        return mysqli_real_escape_string($this->conn, $check);
    }

}
