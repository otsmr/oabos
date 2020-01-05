<?php

namespace parse;


class Time{

    public function start($time){
        $start_date = new \DateTime($time);
        $date = $start_date->diff(new \DateTime());

        if($date->s > 0){
            if($date->s === 1) $time = "1 Sekunde";
            else $time = "$date->s Sekunden";
        }
        if($date->i > 0){
            if($date->i === 1) $time = "1 Minute";
            else $time = "$date->i Minuten";
        }
        if($date->h > 0){
            if($date->h === 1) $time = "1 Stunde";
            else $time = "$date->h Stunden";
        }
        if($date->d > 0){
            if($date->d === 1) $time = "1 Tag";
            else $time = "$date->d Tagen";
        }
        if($date->m > 0){
            if($date->m === 1) $time = "1 Monat";
            else $time = "$date->m Monaten";
        }

        if($date->y > 0){
            if($date->y === 1) $time = "1 Jahr";
            else $time = "$date->y Jahren";
        }

        return $time;
    }

}