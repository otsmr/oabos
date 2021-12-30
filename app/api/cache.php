<?php


class Cache{

    public function load($requestID){

        $path = __DIR__ . "/cache/$requestID.cache";

        // Der Cache ist 30 Minuten gültig
        if(is_file($path) && filemtime($path) < time() - 60 * 30){
            unlink($path);
        }

        if(is_file($path)){

            $content = file_get_contents($path);
            $content = json_decode($content);

            return $content;

        }else{
            return false;
        }

    }

    public function update($requestID, $data){

        $path = __DIR__ . "/cache/$requestID.cache";

        $data = json_encode($data);

        if(@file_put_contents($path, $data)){
            return true;
        }else{
            return false;
        }

    }

}