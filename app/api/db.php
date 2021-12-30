<?php

class DB {

    public function __construct (string $user_id) {

        $root = __DIR__ . "/db/$user_id";

        if(!is_dir($root))
            mkdir($root, 0700);

        $this->root = $root;

    }

    public function update_content ($data) {

        $handle = fopen($this->root . "/feed.json", "w");
        $write = @fwrite ($handle, json_encode($data));
        fclose ($handle);

    }

    public function get_content () {
        if (!is_file($this->root . "/feed.json"))
            return [];
        return @json_decode(file_get_contents($this->root . "/feed.json"));
    }

}