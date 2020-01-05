<?php

$apikey = false;
if (isset($_GET["apikey"])) {
	
	$apikey = $_GET["apikey"];
    require_once __DIR__ . "/api/getFeed.php";
    
	print_r(json_encode([
		"chanel" => $onlyRss,
		"feed"=> $items
    ]));
    
	die();
	
}

require_once __DIR__ . "/api/load.php";

$logged = false;

$link = $CONFIG["odmin_base_url"] . "/login?service=" . $CONFIG["odmin_service_name"];


if(isset($_COOKIE['token'])){
    
    $userID = odmin();
    
    if (!is_null($userID)) {
        $logged = true;
    }
    else header("Location: $link");

} else {
    header("Location: $link");
    die();
}

require_once __DIR__ . "/api/getFeed.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" type="image/png" href="/img/logo-round.png"/>
    <title>Orange Abos - OProjekt</title>

    <link rel="stylesheet" href="fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/list.css">
    <link rel="stylesheet" href="css/dialog.css">
    <link rel="stylesheet" href="css/main.css">

    <script src="js/jquery.min.js"></script>
</head>
<body>
    

    <header>
        <form method="post">
            <button type="submit" value='true' name='refresh' class="refresh"><i class="fas fa-sync"></i></button>
        </form>
    </header>

    <div class="center">

        <aside>
            <div class="icon" id="addFeed" title="Feed hinzufügen"><i class="fas fa-plus"></i></div>

            <div class="favicon"></div>
            <?php if(count($onlyRss) > 0): ?>
            <div class="rss-abos">
                <h3>OAbos</h3>
                <ul>
                <?php foreach ($onlyRss as $item): $item; ?>
                <li item-id="<?php echo $item->id; ?>">
                    <div class="icon"><i class="settings fas fa-cog"></i></div>
                    <div> <?php echo $item->name; ?> </div>
                </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if(count($onlyYT) > 0): ?>
                 <div class="yt-abos">
                    <h3>YouTube Abos</h3>
                    <ul>
                        <?php foreach ($onlyYT as $item): $item; ?>
                            <li item-id="<?php echo $item->id; ?>">
                                <div class="icon"><i class="settings fas fa-cog"></i></div>
                                <div>
                                    <?php echo $item->name; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

        </aside>

        <div class="items">
            <a style="color: #717070; text-decoration: none; margin: 9px 30px -18px; display: block;" href='<?php echo $CONFIG["odmin_base_url"] ?>/api/logout/<?php echo $_COOKIE["token"] ?>?service=<?php echo $CONFIG["odmin_service_name"] ?>'>Abmelden</a>


        <?php


        $names = [];

        foreach($dbFeed as $struct) {
            $names[$struct->id] = $struct->name;
        }


        function generateItems($items, $title){
            global $names;
            if(count($items) === 0) return;
            $timeID = str_replace(" ", "", $title);
            ?>
            <h3 time="<?php echo $timeID ?>"><?php echo $title; ?></h3>
            <ul class='liste' forTime="<?php echo $timeID ?>">

                <?php foreach ($items as $item): $item;
                    $wID = substr(strrchr($item->link, "="), 1);
                    ?>
                    <li class="item" from="<?php echo $item->id ?>" yt-id="<?php echo $wID ?>" class='openVideo'>

                        <img link="img/youtube.php?id=<?php echo $wID ?>" src="img/placeholder.png">
                        <p class='title'> <?php echo $item->title ?> </p>
                        <p class='kanal'> <?php echo $names[$item->id] ?> - vor <?php echo $item->date ?>  </p>

                    </li>
                <?php endforeach; ?>
            </ul>
            <?php
        }

        $today = [];
        $yesterDay = [];
        $lastweek = [];
        $older = [];

        foreach ($items as &$item) {
            $data = explode(" ",  $item->date);

            if(in_array($data[1], ["Sekunde", "Sekunden", "Minute", "Minuten", "Stunde", "Stunden"]))
                array_push($today, $item);
            else if(in_array($data[1], ["Tag"])) array_push($yesterDay, $item);
            else if(in_array($data[1], ["Tagen"])) array_push($lastweek, $item);
            else array_push($older, $item);
            
        }

        generateItems($today, "Heute");
        generateItems($yesterDay, "Gestern");
        generateItems($lastweek, "Letze Woche");
        generateItems($older, "Älter");

        ?>
    </div>
    </div>
    <script src="js/parseRequest.js"></script>
    <script src="js/main.js"></script>
    <script src="js/dialog.js"></script>
</body>
</html>