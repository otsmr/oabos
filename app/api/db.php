<?php

class DB {

    public function __construct (string $user_id) {

        $this->root = __DIR__ . "/../data/user/$user_id";

        if(!is_dir($this->root))
            mkdir($this->root, 0700);

    }

    public function update_content ($data) {

        $handle = fopen($this->root . "/feed.json", "w");
        $write = @fwrite($handle, json_encode($data));
        fclose($handle);

    }

    public function get_content () {
        if (!is_file($this->root . "/feed.json"))
            return [];
        return @json_decode(file_get_contents($this->root . "/feed.json"));
    }

}