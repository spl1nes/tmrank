<?php

include_once __DIR__ . '/phpOMS/Autoloader.php';
include_once __DIR__ . '/db.php';

use phpOMS\Message\Http\HttpRequest;

$request = HttpRequest::createFromSuperglobals();
$current_type = $request->getDataInt('type') ?? 1;
$page = $request->getDataString('page') ?? 'ranking';

if ($page !== 'maps' && $page !== 'user' && $page !== 'user_search' && $page !== 'userinfo') {
    $page = 'ranking';
}

$order = $request->getDataString('order') ?? 'default';
if ($order !== 'finish' && $order !== 'at' && $order !== 'gold' && $order !== 'silver' && $order !== 'bronze' && $order !== 'time') {
    $order = 'default';
}

?>
<!DOCTYPE html>
    <head>
        <link rel="shortcut icon" href="/tpl/favicon.ico" type="image/x-icon">
        <style>html, body { margin: 0; padding: 0; background: #353535; height: 100%; min-height: 100%; font-family: Arial, Helvetica, sans-serif; } body { display: flex; flex-direction: column; align-items: stretch; font-size: .9rem; } main { flex-grow: 1; } footer { padding: 1rem 0 1rem 0; flex-grow: 0; text-align: center; } .floater { margin: 0 auto; width: 90%; max-width: 1024px; color: #fff; line-height: 1.2rem; } .floater+.floater { margin-top: 1rem; } .center { text-align: center; } table { border-collapse: collapse; border-spacing: 0; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); background-color: #fff; margin: 0 auto; width: 100%; } .floater table a { text-decoration: underline dotted; color: inherit; } .floater table a:hover { color: inherit; } th { background-color: #3498db; color: #fff; text-align: left; padding: 12px; font-weight: bold; border-bottom: 2px solid #ccc; } tr:nth-child(even) { background-color: #f2f2f2; } tr:nth-child(odd) { background-color: #e6e6e6; } td { padding: 10px; color: #333; } tr:hover { background-color: #46acf0; color: #fff; } tr:hover a, tr:hover td a { color: #fff; } tr:hover td { color: #fff; } form { margin-left: 10px; display: inline-flex; align-items: center; } input[type="submit"], a.button { text-decoration: none; padding: .5rem 2rem .5rem 2rem; background-color: #3498db; color: #fff; display: inline-block; border: none; font-size: 1rem; display: inline-block; cursor: pointer; } input[type="submit"]:hover, a.button:hover { background-color: #46acf0; color: #fff; } input[type="submit"] { margin-left: 4px; } select, input[type="text"] { color: #000; padding: .5rem 2rem .5rem 2rem; } #ranking_top { position: relative; vertical-align: bottom; } .global_maps_stat { position: absolute; bottom: 0; right: 0; } header { margin-top: 1rem; margin-bottom: 1rem; } header a { text-decoration: none; color: #fff; } #toplogo { flex: 1; display: flex; align-items: center; } #toplogo span { font-size: 1.5rem; margin-left: 1rem; font-weight: 400; } #bottomnav li { color: #fff; text-decoration: none; display: inline-block; } #bottomnav li+li { margin-left: 1rem; } a { color: #3498db; text-decoration: none; } a:hover { color: #46acf0; } #maps .img-container { display: none; position: absolute; margin-top: -200px; right: 10px; } #maps tr:hover .img-container { display: block; } ul { margin: 0; padding: 0; } table thead label { cursor: pointer; } table thead input { display: none; } table thead input+span { display: none; } table thead input:checked+span { display: inline-block; }</style>
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
                New drivers are added once every 24h, just finish any map in the top 10,000 of the map pack and your name will be automatically added. Existing drivers and times are updated once every 24h. Name changes are performed once every week. Help with maintaining the map packs (add/remove maps, adjust scoring, create new categories, ...) by editing the <a href="https://github.com/spl1nes/tmrank/blob/master/maps.csv">maps</a> file or contact me on <a href="https://discord.com/channels/1062368297728884797/1152204810343424030">discord</a>.
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

<script>
    jsOMS.ready(function ()
    {
        const type_selector = document.getElementById('type_selector');
        if (type_selector !== null) {
            type_selector.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('type', type_selector.value);
                window.location.href = url.toString();
            });
        }

        const sort = document.querySelectorAll('table thead input[name="sort"]');
        let length = sort.length;
        for (let i = 0; i < length; ++i) {
            sort[i].addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('order', this.value);
                window.location.href = url.toString();
            });
        }
    });
</script>
<?php
$filePath = __DIR__ . '/stats/impressions.json';
$currentDate = \date('Y-m-d');
$currentHour = \date('H');

if (!\is_file($filePath)) {
    \file_put_contents($filePath, '{}');
}

    $fileData = \file_get_contents($filePath);
    $data = \json_decode($fileData, true);

    if (!isset($data[$currentDate])) {
        $data[$currentDate] = [];
    }

    if (!isset($data[$currentDate][$currentHour])) {
        $data[$currentDate][$currentHour] = 0;
    }

    ++$data[$currentDate][$currentHour];

\file_put_contents($filePath, \json_encode($data));
?>
