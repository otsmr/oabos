<?php

class DB {

    public function __construct (string $user_id) {

        $this->root = __DIR__ . "/../data/user/$user_id";

        if(!is_dir($this->root))
            mkdir($this->root, 0700);

    }

    public function get_api_key() {
      if (!is_file($this->root . "/api_key")) {
        $api_key = bin2hex(random_bytes(32));
        $handle = fopen($this->root . "/api_key", "w");
        $write = @fwrite($handle, $api_key);
        fclose($handle);
      }
      return file_get_contents($this->root . "/api_key");
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

    public function get_watch_progress() {
        $file = $this->root . "/watch_progress.json";
        if (!is_file($file)) {
            return new stdClass();
        }
        $data = @json_decode(file_get_contents($file), true);
        if (!is_array($data)) {
            return new stdClass();
        }
        
        $changed = false;
        $now = time();
        $three_days = 259200; // 3 * 24 * 60 * 60
        
        foreach ($data as $id => $item) {
            if (!isset($item['timestamp']) || ($now - $item['timestamp']) > $three_days) {
                unset($data[$id]);
                $changed = true;
            }
        }
        
        if ($changed) {
            $handle = fopen($file, "w");
            @fwrite($handle, json_encode((object) $data));
            fclose($handle);
        }
        
        return (object) $data;
    }

    public function save_watch_progress($video_id, $time, $duration, $percentage) {
        $progress = (array) $this->get_watch_progress();
        $progress[$video_id] = [
            'time' => (float) $time,
            'duration' => (float) $duration,
            'percentage' => (float) $percentage,
            'timestamp' => time()
        ];
        
        $file = $this->root . "/watch_progress.json";
        $handle = fopen($file, "w");
        @fwrite($handle, json_encode((object) $progress));
        fclose($handle);
    }

}

