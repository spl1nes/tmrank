<?php

include_once __DIR__ . '/phpOMS/Autoloader.php';
include_once __DIR__ . '/db.php';

use phpOMS\Message\Http\HttpRequest;

$request = HttpRequest::createFromSuperglobals();
$current_type = $request->getDataInt('type') ?? 1;
$page = $request->getDataString('page') ?? 'ranking';

if ($page !== 'maps' && $page !== 'user') {
    $page = 'ranking';
}
?>
<!DOCTYPE html>
    <head>
        <link rel="shortcut icon" href="/tpl/favicon.ico" type="image/x-icon">
        <link rel="stylesheet" href="/styles.css">
        <script src="/jsOMS/Utils/oLib.js?v=1.0.0"></script>
    </head>
    <body>
        <header>
            <div class="floater">
                <a id="toplogo" href="/">
                    <img alt="Logo" src="/tpl/logo.png" width="40px">
                    <span>Jingga</span>
                </a>
            </div>
        </header>
        <main>
            <div class="floater center">
                New drivers are added once every 24h, just finish any map in the top 10,000 of the map pack and your name will be automatically added. Existing drivers and times are updated once every 24h. Name changes are performed once every week. Help with maintaining the map packs (add/remove maps, adjust scoring, create new categories, ...) by editing the <a href="https://github.com/spl1nes/tmrank/blob/master/maps.csv">maps</a> file.
            </div>
            <?php include __DIR__ . '/tpl/' . $page . '.php'; ?>
        </main>
        <footer>
            <div class="floater">
                <ul id="bottomnav">
                    <li><a href="https://www.paypal.com/donate/?hosted_button_id=UF37LLDV3Z4DE">Donate</a></li>
                    <li><a href="https://jingga.app/terms">Terms</a></li>
                    <li><a href="https://jingga.app/privacy">Privacy</a></li>
                    <li><a href="https://jingga.app/imprint">Imprint</a></li>
                </ul>
            </div>
        </footer>
</html>
