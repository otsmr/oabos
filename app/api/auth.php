<?php

namespace ODMIN {

    class OAuth {

        private string $users_file;
        private string $sessions_file;
        public object $session;
        public ?string $error_message = null;

        public function __construct(?object $config = null) {
            $this->users_file = __DIR__ . "/../data/users.json";
            $this->sessions_file = __DIR__ . "/../data/sessions.json";

            $this->session = (object) [
                "token" => "",
                "user_id" => "",
                "user_name" => ""
            ];

            $this->ensure_data_files();
        }

        private function ensure_data_files() {
            $dir = dirname($this->users_file);
            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
            }
            if (!is_file($this->users_file)) {
                file_put_contents($this->users_file, json_encode([], JSON_PRETTY_PRINT));
            }
            if (!is_file($this->sessions_file)) {
                file_put_contents($this->sessions_file, json_encode([], JSON_PRETTY_PRINT));
            }
        }

        private function get_users(): array {
            if (!is_file($this->users_file)) {
                return [];
            }
            $content = file_get_contents($this->users_file);
            return json_decode($content, true) ?: [];
        }

        private function save_users(array $users) {
            file_put_contents($this->users_file, json_encode($users, JSON_PRETTY_PRINT));
        }

        private function get_sessions(): array {
            if (!is_file($this->sessions_file)) {
                return [];
            }
            $content = file_get_contents($this->sessions_file);
            return json_decode($content, true) ?: [];
        }

        private function save_sessions(array $sessions) {
            file_put_contents($this->sessions_file, json_encode($sessions, JSON_PRETTY_PRINT));
        }

        public function login(string $username, string $password): bool {
            $username = trim($username);
            if (empty($username) || empty($password)) {
                $this->error_message = "Bitte Benutzername und Passwort eingeben.";
                return false;
            }

            $users = $this->get_users();
            $lower_username = strtolower($username);

            $found_user = null;
            foreach ($users as $user) {
                if (strtolower($user['username']) === $lower_username) {
                    $found_user = $user;
                    break;
                }
            }

            if (!$found_user || !password_verify($password, $found_user['password'])) {
                $this->error_message = "Ungültiger Benutzername oder Passwort.";
                return false;
            }

            // Create session
            $token = bin2hex(random_bytes(32));
            $sessions = $this->get_sessions();

            $sessions[$token] = [
                "user_id" => (string)$found_user['user_id'],
                "user_name" => $found_user['username'],
                "created_at" => time()
            ];
            $this->save_sessions($sessions);

            // Set cookie (30 days)
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            setcookie("odmin_token", $token, time() + 3600 * 24 * 30, "/", "", $secure, true);

            $this->session->token = $token;
            $this->session->user_id = (string)$found_user['user_id'];
            $this->session->user_name = $found_user['username'];

            return true;
        }

        public function register(string $username, string $password, string $confirm_password, string $invite_token = ""): bool {
            $username = trim($username);
            if (empty($username) || empty($password) || empty($confirm_password)) {
                $this->error_message = "Bitte alle Felder ausfüllen.";
                return false;
            }

            // Check invite token loaded from config.json
            $config_file = __DIR__ . "/../data/config.json";
            if (!is_file($config_file)) {
                $default_config = [
                    "invite_token" => "YT-ABOS-INVITE"
                ];
                file_put_contents($config_file, json_encode($default_config, JSON_PRETTY_PRINT));
            }
            $config_data = json_decode(file_get_contents($config_file), true);
            $expected_token = isset($config_data['invite_token']) ? trim((string)$config_data['invite_token']) : "";

            if (!empty($expected_token) && trim($invite_token) !== $expected_token) {
                $this->error_message = "Ungültiges Einladungstoken.";
                return false;
            }

            if ($password !== $confirm_password) {
                $this->error_message = "Die Passwörter stimmen nicht überein.";
                return false;
            }

            if (strlen($password) < 6) {
                $this->error_message = "Das Passwort muss mindestens 6 Zeichen lang sein.";
                return false;
            }

            // Simple username validation
            if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
                $this->error_message = "Der Benutzername darf nur Buchstaben, Zahlen und Unterstriche enthalten (3-20 Zeichen).";
                return false;
            }

            $users = $this->get_users();
            $lower_username = strtolower($username);

            foreach ($users as $user) {
                if (strtolower($user['username']) === $lower_username) {
                    $this->error_message = "Dieser Benutzername ist bereits vergeben.";
                    return false;
                }
            }

            // Generate unique sequential integer user ID (represented as string)
            $max_id = 0;
            foreach ($users as $user) {
                if (isset($user['user_id']) && (int)$user['user_id'] > $max_id) {
                    $max_id = (int)$user['user_id'];
                }
            }
            $user_id = (string)($max_id + 1);

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $users[] = [
                "user_id" => $user_id,
                "username" => $username,
                "password" => $hashed_password,
                "created_at" => time()
            ];

            $this->save_users($users);

            // Automatically log in the registered user
            return $this->login($username, $password);
        }

        public function init_session_from_cookie(): bool {
            if (!isset($_COOKIE["odmin_token"])) return false;

            $token = (string) $_COOKIE["odmin_token"];
            $sessions = $this->get_sessions();

            if (!isset($sessions[$token])) {
                $this->handle_logout();
                return false;
            }

            $this->session->token = $token;
            $this->session->user_id = (string)$sessions[$token]["user_id"];
            $this->session->user_name = (string)$sessions[$token]["user_name"];

            return true;
        }

        public function handle_logout(int $status = 0): void {
            if (isset($_COOKIE["odmin_token"])) {
                $token = (string) $_COOKIE["odmin_token"];
                $sessions = $this->get_sessions();
                if (isset($sessions[$token])) {
                    unset($sessions[$token]);
                    $this->save_sessions($sessions);
                }
            }

            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            setcookie("odmin_token", "", time() - 3600, "/", "", $secure, true);
            $this->session->token = "";
            $this->session->user_id = "";
            $this->session->user_name = "";
        }

        public function is_logged_in(): bool {
            return !empty($this->session->token);
        }

        public function get_signin_url(string $continue = "/"): string {
            return "index.php?action=login";
        }

        public function get_signout_url(string $continue = "/"): string {
            return "index.php?action=logout";
        }

        public function handle_oauth_code(string $code): bool {
            return false;
        }

        public function handle_continue_location(string $default_location): void {
            header("Location: $default_location");
            die();
        }
    }
}