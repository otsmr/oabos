<?php

namespace Read;

class Reader{


    private function merge($a, $b){
        foreach ($b as &$c) { array_push($a, $c); }
        return $a;
    }

    private function sort($items){
        
        uasort($items, function($a, $b) {
            $times = ["Sekunde", "Sekunden", "Minute", "Minuten", "Stunde", "Stunden", "Tag", "Tagen", "Woche", "Wochen", "Monat", "Monaten", "Jahr", "Jahren"];
        
            $dateA = explode(" ",  $a['date']);
            $keyA = array_search($dateA[1], $times);
        
            $dateB = explode(" ",  $b['date']);
            $keyB = array_search($dateB[1], $times);
        
            if($dateA[1][0] === $dateB[1][0] ) return $dateA[0] > $dateB[0];
            else return $keyA > $keyB;
            
        });
        return $items;
    }

    public function getFeed($ytIDs = [], $rssUrls = []){
        $items = [];

        if(count($ytIDs) > 0){

            require_once __DIR__ . "/youtube.php";
            $yt = new \YouTube\TimeLine();
        
            $items = $this->merge($items, $yt->get($ytIDs));
        
        }
        
        if(count($rssUrls) > 0){
        
            require_once __DIR__ . "/rss.php";
            $rss = new \RSS\TimeLine();
        
            $items = $this->merge($items, $rss->get($rssUrls));

        }

        $items = $this->sort($items);
        return json_decode(json_encode($items));

    }

}