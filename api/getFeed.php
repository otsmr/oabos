
<?php

if(!$apikey && !isset($_COOKIE["token"])) die(json_encode([
    "error" => "Kein User angemeldet"
]));

/*
#######################################
    Load Files
#######################################
*/
require_once __DIR__ . "/load.php";
require_once __DIR__ . "/reader/read.php";
require_once __DIR__ . "/cache/cache.php";


class LoadFeed{

    private $db;
    private $a;
    private $odmin;
    private $userID;

    public function __construct(){
        global $CONFIG, $apikey;
         
		$this->db = new DB();
		$this->o = new Output();
		
		if ($apikey) {
			
			$user = $this->db->get("SELECT userID FROM feed WHERE apikey = '$apikey'");
			if (!$user["userID"]) {
				$o->error("Du bist nicht mehr eingeloggt.");
			}
			$this->userID = $user["userID"];
			
		} else {

            $userID = odmin();

            if (is_null($userID)) {
                $o->error("Du bist nicht mehr eingeloggt.");
            }
            
            $this->userID = $userID;
			
		}

    }

    public function getData(){

        if($feed = $this->db->get("SELECT feed FROM feed WHERE userID = '$this->userID'")){
            return json_decode($feed["feed"]);
        }else{
            return [];
        }

    }

    public function getFeed(){

        return $this->getData();

    }
}


$loadFeed = new LoadFeed();

$dbFeed = $loadFeed->getFeed();

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
    $items =$data;
    
}else{

    $reader = new \Read\Reader();
    $items = $reader->getFeed($onlyYT, $onlyRss);

    $items = json_decode(json_encode($items));

    $cache->update($requestID, $items);

}

