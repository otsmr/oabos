<?php

require_once __DIR__ . "/api/auth.php";
$odmin = new \ODMIN\OAuth();

$auth_error = "";
$default_mode = "login";

// Handle Login / Registration POST requests
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["auth_action"])) {
    $action = $_POST["auth_action"];
    if ($action === "login") {
        $username = $_POST["username"] ?? "";
        $password = $_POST["password"] ?? "";
        if ($odmin->login($username, $password)) {
            header("Location: index.php");
            die();
        } else {
            $auth_error = $odmin->error_message;
            $default_mode = "login";
        }
    } elseif ($action === "register") {
        $username = $_POST["username"] ?? "";
        $password = $_POST["password"] ?? "";
        $confirm_password = $_POST["confirm_password"] ?? "";
        $invite_token = $_POST["invite_token"] ?? "";
        if ($odmin->register($username, $password, $confirm_password, $invite_token)) {
            header("Location: index.php");
            die();
        } else {
            $auth_error = $odmin->error_message;
            $default_mode = "register";
        }
    }
}

$odmin->init_session_from_cookie();

// Handle Logout GET requests
if (isset($_GET["action"]) && $_GET["action"] === "logout") {
    $odmin->handle_logout(1);
    header("Location: index.php");
    die();
}

if ($odmin->is_logged_in()) {
  $logged_in_odmin_id = $odmin->session->user_id;
  require_once "api/feed.php";
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>OneAbo</title>

    <link rel="shortcut icon" type="image/png" href="img/favicon.png"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php if (!$odmin->is_logged_in()): ?>

    <div class="login-wrapper">
        <main class="auth-card" id="authContainer" data-mode="<?php echo $default_mode; ?>">
            <!-- Brand Panel (Left Side) -->
            <div class="auth-brand-side">
                <div class="brand-glow brand-glow-1"></div>
                <div class="brand-glow brand-glow-2"></div>
                
                <div class="brand-content">
                    <div class="logo-badge">OneAbo</div>
                    <h2 class="brand-headline">Dein YouTube Feed.<br><span class="accent-text">Ohne Ablenkung.</span></h2>
                    <p class="brand-subline">Erlebe deine Abonnements in einem schnellen, datensparenden und werbefreien Dashboard. Keine Algorithmen, nur deine Kanäle.</p>
                    
                    <div class="brand-visual">
                        <div class="visual-card visual-card-1">
                            <div class="visual-avatar"></div>
                            <div class="visual-lines">
                                <div class="visual-line line-long"></div>
                                <div class="visual-line line-short"></div>
                            </div>
                            <div class="visual-badge">Abonniert</div>
                        </div>
                        <div class="visual-card visual-card-2">
                            <div class="visual-play-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 0 24 24" width="20px" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                            <div class="visual-progress"></div>
                        </div>
                    </div>
                </div>
                
                <footer class="brand-footer">
                    <p class='desc'>Ein Service von <a href='https://tsmr.eu' target="_blank">tsmr.eu</a></p>
                </footer>
            </div>

            <!-- Form Panel (Right Side) -->
            <div class="auth-form-side">
                <!-- Loading Overlay -->
                <div class="auth-loading-overlay" id="authLoadingOverlay">
                    <div class="spinner"></div>
                    <p id="authLoadingText">Anmeldung läuft...</p>
                </div>

                <div class="auth-form-content">
                    <header class="auth-form-header">
                        <h3 class="auth-title-login">Willkommen zurück</h3>
                        <h3 class="auth-title-register">Konto erstellen</h3>
                        <p class="auth-subtitle-login">Melde dich an, um auf deine Feeds zuzugreifen.</p>
                        <p class="auth-subtitle-register">Tritt bei und verwalte deine Kanäle.</p>
                    </header>

                    <?php if (!empty($auth_error)): ?>
                        <div class="auth-error-banner">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 0 24 24" width="20px" fill="var(--color-error)"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                            <span><?php echo htmlspecialchars($auth_error); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="post" class="auth-form login-form">
                        <input type="hidden" name="auth_action" value="login">
                        <div class="input-group">
                            <input type="text" name="username" placeholder="Benutzername" autocomplete="username" required>
                        </div>
                        <div class="input-group">
                            <input type="password" name="password" placeholder="Passwort" autocomplete="current-password" required>
                        </div>
                        <button type="submit" class="button button-primary">Anmelden</button>
                        <p class="auth-switch-text">Noch kein Konto? <a href="#" id="switchToRegister">Registrieren</a></p>
                    </form>

                    <!-- Register Form -->
                    <form method="post" class="auth-form register-form">
                        <input type="hidden" name="auth_action" value="register">
                        <div class="input-group">
                            <input type="text" name="username" placeholder="Benutzername" autocomplete="username" required>
                        </div>
                        <div class="input-group">
                            <input type="password" name="password" placeholder="Passwort" autocomplete="new-password" required>
                        </div>
                        <div class="input-group">
                            <input type="password" name="confirm_password" placeholder="Passwort wiederholen" autocomplete="new-password" required>
                        </div>
                        <div class="input-group">
                            <input type="text" name="invite_token" placeholder="Einladungstoken" required autocomplete="off">
                        </div>
                        <button type="submit" class="button button-primary">Registrieren</button>
                        <p class="auth-switch-text">Bereits ein Konto? <a href="#" id="switchToLogin">Anmelden</a></p>
                    </form>
                </div>
            </div>
        </main>
    </div>

<?php else: ?>

    <!-- Your API-Key: <?php echo $db->get_api_key(); ?> -->
    <!-- Your User-ID: <?php echo $odmin->session->user_id; ?> -->
    <!-- API-LINK: https://oabos.de/api.php?api_key=<?php echo $db->get_api_key(); ?>&user_id=<?php echo $odmin->session->user_id; ?> -->

    <div class="center app-layout">

        <!-- Sidebar overlay for mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>OneAbo</h3>
                <div class="sidebar-actions">
                    <form method="post">
                        <button type="submit" value="true" name="refresh" class="add-icon-btn" aria-label="Feed aktualisieren">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 0 24 24" width="20px" fill="currentColor"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
                        </button>
                    </form>
                    <button class="add-icon-btn" onclick="addToFeed()" aria-label="Kanal hinzufügen">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 0 24 24" width="20px" fill="currentColor"><path d="M0 0h24v24H0z" fill="none"/><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    </button>
                </div>
            </div>

            <div class="sidebar-scrollable">
                <ul class="sub-list">
                    <?php foreach ($yt_abos as $item): ?>
                        <li class="sub-item" onclick="removeFromFeed('<?php echo htmlspecialchars($item->id); ?>')">
                            <span class="sub-name"><?php echo htmlspecialchars($item->name); ?></span>
                            <div class="trash-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 0 24 24" width="18px" fill="currentColor"><path d="M0 0h24v24H0z" fill="none"/><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-footer">
                <div class="user-info">
                    <span class="user-avatar"><?php echo strtoupper(substr($odmin->session->user_name, 0, 1)); ?></span>
                    <span class="username"><?php echo htmlspecialchars($odmin->session->user_name); ?></span>
                </div>
                <a class="logoutbtn-link" href='<?php echo $odmin->get_signout_url(); ?>'>
                    <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 0 24 24" width="18px" fill="currentColor"><path d="M0 0h24v24H0z" fill="none"/><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                    Abmelden
                </a>
            </div>
        </aside>

        <div class="items content-area">
            <header class="mobile-header">
                <button id="mobileSidebarToggle" class="mobile-menu-btn" aria-label="Menü anzeigen">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                    </svg>
                </button>
                <h2 class="mobile-title">OneAbo</h2>
                <div class="mobile-header-spacer"></div>
            </header>

            <div class="feed-wrapper">
                <ul class='liste feed-grid'>
                    <?php foreach ($feed as &$item): ?>
                        <li class="item video-card" from="<?php echo htmlspecialchars($item->id); ?>" yt-id="<?php echo htmlspecialchars($item->id); ?>">
                            <div class="thumbnail-wrapper">
                                <img class="video-thumbnail" link="<?php echo htmlspecialchars($item->img); ?>" src="img/placeholder.png" alt="Video Thumbnail">
                                <div class="play-overlay">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 0 24 24" width="48px" fill="#ffffff"><path d="M0 0h24v24H0z" fill="none"/><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                            <div class="video-info">
                                <h4 class='title video-title' title="<?php echo htmlspecialchars($item->title); ?>">
                                    <?php echo htmlspecialchars($item->title); ?>
                                </h4>
                                <p class='kanal video-meta'>
                                    <span class="channel-name"><?php echo htmlspecialchars($item->channel); ?></span>
                                    <span class="meta-dot">&bull;</span>
                                    <span class="time"><?php echo htmlspecialchars($item->date); ?> </span>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </div>

    <!-- Add Channel Modal -->
    <div id="addChannelModal" class="modal-backdrop">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Kanal hinzufügen</h3>
                <button class="modal-close-btn" id="closeAddChannelModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addChannelForm">
                    <p class="modal-instructions">Füg die URL eines YouTube-Kanals (z.B. <code>youtube.com/@Kanalname</code>) oder eines Videos ein. Der Kanal wird automatisch ermittelt.</p>
                    <div class="input-group">
                        <input type="text" id="channelUrlInput" placeholder="https://www.youtube.com/watch?v=..." required autocomplete="off">
                    </div>
                    <div id="addChannelError" class="modal-error-message" style="display: none;"></div>
                    <div class="modal-actions">
                        <button type="button" class="button button-secondary" id="cancelAddChannel">Abbrechen</button>
                        <button type="submit" class="button button-primary" id="submitAddChannel">Hinzufügen</button>
                    </div>
                </form>
                <div id="addChannelLoading" class="modal-loading" style="display: none;">
                    <div class="spinner"></div>
                    <p>Kanaldaten werden geladen...</p>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

    <?php if ($odmin->is_logged_in()): ?>
        <script>
            window.serverWatchProgress = <?php echo json_encode($db->get_watch_progress()); ?>;
        </script>
    <?php endif; ?>
    <script src="main.js"></script>

</body>
</html>
