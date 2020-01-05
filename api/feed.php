<?php

namespace API;

if(!isset($_COOKIE["token"])) die(json_encode([
    "error" => "Kein User angemeldet"
]));

if(!isset($_POST["type"])) die(json_encode([
    "error" => "Fehlerhafte Anfrage"
]));

require_once __DIR__ . "/load.php";


class UpdateDB{

    public function __construct(){
        global $CONFIG;

        $this->o = new \Output();

        $userID = odmin();

        if (is_null($userID)) {
            $o->error("Du bist nicht mehr eingeloggt.");
        }
        
        $this->userID = $userID;

        $this->db = new \DB();

    }

    private function startsWith($haystack, $needle){
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    private function getRandID($l = 9, $c = "1234567890", $u = FALSE) {
        for ($s = '', $i = 0, $z = strlen($c)-1; $i < $l; $x = rand(0,$z), $s .= $c{$x}, $i++);
        return $s;
    }

    public function getData(){

        if($feed = $this->db->get("SELECT feed FROM feed WHERE userID = '$this->userID'")){
            return json_decode($feed["feed"]);
        }else{
            return [];
        }

    }

    public function removeItem($id){

        $data = $this->getData();

        $new = [];

        foreach ($data as $key => $item) {
            if($item->id != $id){
                array_push($new, $item);
            }
        }

        $new = json_encode($new);

        if($this->db->set("UPDATE feed SET `feed` = '$new' WHERE userID = '$this->userID' ")){
            $this->o->success("Gelöscht");
        }else{
            $this->o->error($id);
        }

    }

    public function addFeed($name, $feed){

        $feed = $this->db->check($feed);
        $name = $this->db->check($name);

        if($this->startsWith($feed, "http")){
            $type = "rss";
        }else{

            $site = "https://www.youtube.com/feeds/videos.xml?channel_id=$feed";
            $content = @file_get_contents($site);

            if (!$content) {

                $site = "https://www.youtube.com/feeds/videos.xml?user=$feed";
                $content = @file_get_contents($site);

                if (!$content) $this->o->error($name);
                
            }
    
            $feed = $site;
            $type = "rss";
        }


        $data = $this->getData();

        array_push($data, [
            "id" => $this->getRandID(15),
            "name" => $name,
            "src" => $feed,
            "type" => $type
        ]);

        $data = json_encode($data);

        $is = $this->db->get("SELECT COUNT(*) FROM feed WHERE userID = '$this->userID'");

        if($is["COUNT(*)"] == 0){

            if($this->db->set("INSERT INTO feed (userID, feed) VALUES ('$this->userID', '$data')")){
                $this->o->success("Hinzugefügt");
            }else{
                $this->o->error($id);
            }

        }else{

            if($this->db->set("UPDATE feed SET `feed` = '$data' WHERE userID = '$this->userID' ")){
                $this->o->success("Hinzugefügt");
            }else{
                $this->o->error($id);
            }
        }


    }

}

$u = new UpdateDB();

switch ($_POST["type"]) {
    case 'removeItem': $u->removeItem($_POST["id"]); break;
    case 'addFeed': $u->addFeed($_POST["name"], $_POST["id"]); break;

}