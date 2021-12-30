<?php

require_once __DIR__ . "/api/odmin/init.php";

$odmin->init_session_from_cookie();

if (!$odmin->is_logged_in()) {
    header("Location: " . $odmin->get_signin_url());
    die();
}

require_once "api/feed.php";

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>YT Abos</title>
    
    <link rel="shortcut icon" type="image/png" href="/img/favicon.png"/>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    
    <header>
        <form method="post">
            <button type="submit" value='true' name='refresh' class="refresh">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#fff"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
            </button>
        </form>
    </header>

    <div class="center">

        <aside>

            <div class="icon" onclick="addToFeed()">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#FFFFFF"><path d="M0 0h24v24H0z" fill="none"/><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            </div>

            <div class="favicon"></div>

            <div class="yt-abos">
                <h3>YouTube Abos</h3>
                <ul>
                    <?php foreach ($yt_abos as $item): $item; ?>
                        <li onclick="removeFromFeed('<?php echo $item->id; ?>')">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#FFFFFF"><path d="M0 0h24v24H0z" fill="none"/><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            </div>
                            <div>
                                <?php echo $item->name; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>  

        </aside>

        <div class="items">

            <a class="logoutbtn" href='<?php echo $odmin->get_signout_url(); ?>'>Abmelden</a>
            
            <br /><br /><br />

            <ul class='liste'>
                <?php foreach ($feed as &$item):
                    $wID = substr(strrchr($item->link, "="), 1);
                    ?>
                    <li class="item" from="<?php echo $item->id ?>" yt-id="<?php echo $wID ?>" class='openVideo'>

                        <img link="img/youtube.php?id=<?php echo $wID ?>" src="img/placeholder.png">
                        <p class='title'>
                            <?php echo $item->title ?>
                        </p>
                        <p class='kanal'>
                            <?php echo $item->channel_name; ?> - vor <?php echo $item->date; ?> 
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>

        </div>
    </div>
    
    <script src="main.js"></script>

</body>
</html>