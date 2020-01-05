<?php

namespace RSS;


require_once __DIR__ . "/lib/parseTime.php";

require_once __DIR__ . "/lib/simplepie/autoloader.php";


class TimeLine{

    public function __construct(){
        

    }

    public function get($rssUrls){

        $rssFeads = [];

        foreach ($rssUrls as &$item) {

            $feed = new \SimplePie();
            $feed->set_feed_url($item->src);
            $feed->init();

            $favicon = $this->getFavicon($item->src);

            foreach($feed->get_items(0, 0) as $key=>$feedItem){

                    array_push($rssFeads, [
                        "id" => $item->id,
                        "link" => (string) $feedItem->get_link(),
                        "favivon" => $favicon,
                        // "author" => (string) $author[0]->name,
                        // "name" => (string) $title ,
                        "title" => (string) $feedItem->get_title(),
                        "desc" => $this->getDesc((string) $feedItem->get_description()),
                        "img" => $this->getImage((string) $feedItem->get_content()),
                        "date" => \parse\Time::start($feedItem->get_date()),
                    ]);
            }
        
        }
        return $rssFeads;
        
    }

    public function getImage($html){

        $from = "img src=\"";
        $to = "\"";

        $html = substr($html, strpos($html, $from) + strlen($from));
        $src = substr($html, 0 , -strlen(substr($html, strpos($html, $to) - strlen($html))));

        if($src !== "" && $src[0] === "h"){
            return $src;
        }else{
            return "";
        }

    }

    private function getDesc($html){

        //Tags werden entfernt
        $html = preg_replace("/(<(.*?)>)/i", " ", $html);

        return htmlspecialchars($html);

    }

    private function getFavicon($url){

        $elems = parse_url($url);
        $domain = explode('.', $elems['host']);
        $domain = $domain[count($domain)-2].".".$domain[count($domain)-1];

        return $domain;

    }

}