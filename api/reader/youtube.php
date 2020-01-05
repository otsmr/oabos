<?php


namespace YouTube;

require_once __DIR__ . "/lib/header.php";

class Get{

    public function load($id){

        $url = "https://www.youtube.com/channel/$id/videos";
        $html = @file_get_contents($url, false, \Header\Get::getHeader());

        if(!$html){

            $url = "https://www.youtube.com/user/$id/videos";
            $html = @file_get_contents($url, false, \Header\Get::getHeader());

        }

        if(!$html || strpos($html, "channels-browse-content-grid") === false){
            return false;
        }else{
            return $html;
        }

    }

}

class Render{

    private function getID($string){
        return str_replace(["href=\"/watch?v=", "\"", " "], "", substr($string, 0, 28));
    }

    private function get($string, $from, $to){

        $string = substr($string, strpos($string, $from) + strlen($from));
        return substr($string, 0 , -strlen(substr($string, strpos($string, $to) - strlen($string))));
    }

    private function getFrom($string){
        $date = substr($string, strpos($string, 'vor ') + 4);

        if(strlen($date) + 5 === strlen($string)){

            if (strpos($date, 'PremiumYouTube Premium abonnieren') !== false)  $date = "Premium";
            else $date = "Jetzt Live";
    
        }

        return str_replace(["<li >Untertitel", "Wird geladen..."], "", $date );
    }

    private function getTime($string){

        $isLive = $this->get($string, 'Dauer: ', '</h3>');

        if(strlen($isLive) > 50){


            $isLive = "Jetzt Live";
        }

        return $isLive;
    }

    private function getCalls($string){

        
        $isLive = $this->get($string, '</h3>', 'vor');

        if(strlen($isLive) === 0){
            $isLive = $this->get($string, '</a>', '>Jetzt live');
            $isLive = $this->get($isLive, '</a>', '<li');
            $isLive = $this->get($isLive, '</a>', 'Zuschauer');

            $isLive = $isLive . " Zuschauer";
        }

        return $isLive;
    }



    public function html($content, $itemID){


        $videos = [];


        $ytName = $this->get($this->get($content, "channel-title", "</span>"), "title=\"", "\"");

        //HTML wird bist zu der Video-Liste entfernt (channels-browse-content-grid) ID von der Videoliste
        $content = substr($content, strpos($content, '<ul id="channels-browse-content-grid"'));


        //Müssen entfernt werden
        $content = str_replace("\n", "", $content);
        $content = str_replace("  ", "", $content);
        $content = str_replace("<li>", "", $content);
        $content = str_replace("</li>", "", $content);


        // Attribute und Tags die entfernt werden
        $tags = ["div", "span", "ul", "button" ];
        $attribute = [ "class", "alt", "style", "width", "data-ytimg", "aria-label", "data-video-ids", "onload", "role", "data-visibility-tracking", "aria-hidden", "data-action", "onclick", "dir", "aria-describedby", "rel", "data-sessionlink", "data-context-item-id", "id" ];


        //Entfernen der Tags
        foreach ($tags as &$tag) {
            $content = preg_replace("/(<$tag(.*?)>)/i", "", $content);
            $content = preg_replace("/(<\/$tag(.*?)>)/i", "", $content);
        }

        // Entfernen der Attribute
        foreach ($attribute as &$attr) {
            $content =  preg_replace("/($attr=\"(.*?)\")/i", "", $content);
        }


        // Der Tracking url im Img entfernt
        $content = preg_replace('/(.jpg(.*?)")/i', ".jpg\"", $content);

        //Das Ende Video Liste wird entfernt
        $content = preg_replace('/(Mehr Videos laden+).*/i', "", $content);
        $content = preg_replace('/(<a href="\/"  title="YouTube+).*/i', "", $content);

        //Video Liste wird in die einzenlen Videos zerlegt
        $content = explode("<li ><a ", $content);





        foreach ($content as &$item) {

            $id = $this->getID($item);

            if(!$id || $id === "" || $id === ">" || $id === "channels-browse-content-grid") continue;

            $video = [
                "id" => $itemID,
                "ytID" => $id,
                "link" => "https://www.youtube.com/watch?v=$id",
                "name" => $ytName,
                "favivon" => "youtube.com",
                "title" => $this->get($item, 'title="', '"'),
                "img" => $this->get($item, 'src="', '"'),
                "time" => $this->getTime($item),
                "calls" => $this->getCalls($item),
                "date" => $this->getFrom($item),
            ];

            array_push($videos, $video);

            // die();
        }

        return $videos;

    }

}



class TimeLine{

    public function __construct(){

        $this->render = new Render();
        $this->get = new Get();

    }

    public function get($ytIDs){

        $videos = [];

        foreach ($ytIDs as &$item) {

            $html = $this->get->load($item->src);

            $video = [];

            if($html) $video = $this->render->html($html, $item->id);

            foreach ($video as &$vid) {
        
                array_push($videos, $vid);
        
            }
        
        }

        return $videos;
        
    }

}
