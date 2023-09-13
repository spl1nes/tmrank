<?php

include __DIR__ . '/../../phpOMS/Autoloader.php';
include __DIR__ . '/db.php';
include __DIR__ . '/config.php';

use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\Rest;
use phpOMS\Uri\HttpUri;

function authenticate($email, $password)
{
    // Live Authentication
    $request = new HttpRequest(new HttpUri('https://public-ubiservices.ubi.com/v3/profiles/sessions'));
    $request->header->set('Content-Type', 'application/json');
    $request->header->set('Ubi-AppId', '86263886-327a-4328-ac69-527f0d20a237');
    $request->header->set('Authorization', 'Basic ' . \base64_encode($email . ':' . $password));
    $request->header->set('User-Agent', 'Map pack ranking / ' . $email);
    $request->setMethod('POST');
    $request->data['audience'] = 'NadeoLiveServices';
    $response = Rest::request($request);

    $request = new HttpRequest(new HttpUri('https://prod.trackmania.core.nadeo.online/v2/authentication/token/ubiservices'));
    $request->header->set('Content-Type', 'application/json');
    $request->header->set('Authorization', 'ubi_v1 t=' . \trim($response->data['ticket'] ?? ''));
    $request->header->set('User-Agent', 'Map pack ranking / ' . $email);
    $request->setMethod('POST');
    $request->data['audience'] = 'NadeoLiveServices';

    return Rest::request($request);
}

$authResponse3 = authenticate($email, $password);

$MAX_DRIVER_PER_MAP = 100;

$time = \time();

$maps = MapMapper::getAll()->execute();
$lastId = 0;
$drivers = [];

foreach ($maps as $map) {
    $lastMapId = 0;
    $names = [];

    do {
        $nameRequest = new HttpRequest(new HttpUri('https://live-services.trackmania.nadeo.live/api/token/leaderboard/group/Personal_Best/map/' . $map->uid . '/top?length=50&onlyWorld=true&offset=' . $lastMapId));
        $nameRequest->header->set('Content-Type', 'application/json');
        $nameRequest->header->set('Authorization', 'nadeo_v1 t=' . \trim($authResponse3->data['accessToken'] ?? ''));
        $nameRequest->header->set('User-Agent', 'Map pack ranking / ' . $email);
        $nameRequest->data['audience'] = 'NadeoLiveServices';
        $nameRequest->setMethod('GET');
        $nameResponse = Rest::request($nameRequest);

        if ($nameResponse->header->status !== 200) {
            echo "Invalid name response for " . $map->uid . "\n";

            $authResponse3 = authenticate($email, $password);

            $nameRequest = new HttpRequest(new HttpUri('https://live-services.trackmania.nadeo.live/api/token/leaderboard/group/Personal_Best/map/' . $map->uid . '/top?length=50&onlyWorld=true&offset=' . $lastMapId));
            $nameRequest->header->set('Content-Type', 'application/json');
            $nameRequest->header->set('Authorization', 'nadeo_v1 t=' . \trim($authResponse3->data['accessToken'] ?? ''));
            $nameRequest->header->set('User-Agent', 'Map pack ranking / ' . $email);
            $nameRequest->data['audience'] = 'NadeoLiveServices';
            $nameRequest->setMethod('GET');
            $nameResponse = Rest::request($nameRequest);

            if ($nameResponse->header->status !== 200) {
                \sleep(1);

                continue;
            }
        }

        $names = $nameResponse->data;

        foreach (($names['tops'][0]['top'] ?? []) as $name) {
            // @var Driver $driver
            $driver = DriverMapper::get()->where('uid', $name['accountId'])->execute();
            if ($driver->id === 0) {
                $driver = new Driver();
                $driver->uid = $name['accountId'];

                DriverMapper::create()->execute($driver);
            }
        }

        $lastMapId += 50;
    } while (!empty($names) && \count($names['tops'][0]['top'] ?? []) > 0);
}
