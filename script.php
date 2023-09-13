<?php

include __DIR__ . '/../../phpOMS/Autoloader.php';
include __DIR__ . '/db.php';
include __DIR__ . '/config.php';

use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\Rest;
use phpOMS\Uri\HttpUri;

function authenticate($email, $password)
{
    // Service Authentication
    $request = new HttpRequest(new HttpUri('https://public-ubiservices.ubi.com/v3/profiles/sessions'));
    $request->header->set('Content-Type', 'application/json');
    $request->header->set('Ubi-AppId', '86263886-327a-4328-ac69-527f0d20a237');
    $request->header->set('Authorization', 'Basic ' . \base64_encode($email . ':' . $password));
    $request->header->set('User-Agent', 'Map pack ranking / ' . $email);
    $request->setMethod('POST');
    $request->data['audience'] = 'NadeoServices';
    $response = Rest::request($request);

    $request = new HttpRequest(new HttpUri('https://prod.trackmania.core.nadeo.online/v2/authentication/token/ubiservices'));
    $request->header->set('Content-Type', 'application/json');
    $request->header->set('Authorization', 'ubi_v1 t=' . \trim($response->data['ticket'] ?? ''));
    $request->header->set('User-Agent', 'Map pack ranking / ' . $email);
    $request->setMethod('POST');
    $request->data['audience'] = 'NadeoServices';

    return Rest::request($request);
}

$authResponse2 = authenticate($email, $password);

$MAX_DRIVER_PER_MAP = 100;

$time = \time();

$maps = MapMapper::getAll()->execute();
$lastId = 0;
$drivers = [];

while (true) {
    $drivers = DriverMapper::getAll()
        ->where('id', $lastId, '>')
        ->sort('id', 'ASC')
        ->limit($MAX_DRIVER_PER_MAP)
        ->execute();

    if (empty($drivers) || \reset($drivers)->id === 0) {
        break;
    }

    $driversUid = [];

    foreach ($drivers as $driver) {
        $driversUid[] = $driver->uid;
    }

    // maps to check
    foreach ($maps as $map) {
        $mapRequest = new HttpRequest(new HttpUri('https://prod.trackmania.core.nadeo.online/mapRecords/?accountIdList=' . \implode(',', $driversUid) . '&mapIdList=' . $map->nid));
        $mapRequest->header->set('Content-Type', 'application/json');
        $mapRequest->header->set('Authorization', 'nadeo_v1 t=' . \trim($authResponse2->data['accessToken'] ?? ''));
        $mapRequest->header->set('User-Agent', 'Map pack ranking / ' . $email);
        $mapRequest->data['audience'] = 'NadeoServices';
        $mapRequest->setMethod('GET');
        $mapResponse = Rest::request($mapRequest);

        if ($mapResponse->header->status !== 200) {
            echo "Invalid time response for " . $map->uid . "\n";

            $authResponse2 = authenticate($email, $password);

            $mapRequest = new HttpRequest(new HttpUri('https://prod.trackmania.core.nadeo.online/mapRecords/?accountIdList=' . \implode(',', $driversUid) . '&mapIdList=' . $map->nid));
            $mapRequest->header->set('Content-Type', 'application/json');
            $mapRequest->header->set('Authorization', 'nadeo_v1 t=' . \trim($authResponse2->data['accessToken'] ?? ''));
            $mapRequest->header->set('User-Agent', 'Map pack ranking / ' . $email);
            $mapRequest->data['audience'] = 'NadeoServices';
            $mapRequest->setMethod('GET');
            $mapResponse = Rest::request($mapRequest);

            if ($mapResponse->header->status !== 200) {
                \sleep(1);

                continue;
            }
        }

        foreach ($mapResponse->data as $driver) {
            if ($driver === '') {
                continue;
            }

            $finish = FinishMapper::get()
                ->where('map', $map->nid)
                ->where('driver', $driver['accountId'])
                ->execute();

            if ($finish->id === 0) {
                $finish = new Finish();
                $finish->driver = $driver['accountId'];
                $finish->map = $map->nid;
                $finish->finish_time = (int) $driver['recordScore']['time'];

                if ($finish->finish_time < $map->at_time) {
                    $finish->finish_score = $map->at_score;
                } elseif ($finish->finish_time < $map->gold_time) {
                    $finish->finish_score = $map->gold_score;
                } elseif ($finish->finish_time < $map->silver_time) {
                    $finish->finish_score = $map->silver_score;
                } elseif ($finish->finish_time < $map->bronze_time) {
                    $finish->finish_score = $map->bronze_score;
                } else {
                    $finish->finish_score = $map->finish_score;
                }

                FinishMapper::create()->execute($finish);
            } elseif ($finish->finish_time !== ((int) $driver['recordScore']['time'])
                && ((int) $driver['recordScore']['time']) > 0
            ) {
                $finish->finish_time = (int) $driver['recordScore']['time'];

                if ($finish->finish_time < $map->at_time) {
                    $finish->finish_score = $map->at_score;
                } elseif ($finish->finish_time < $map->gold_time) {
                    $finish->finish_score = $map->gold_score;
                } elseif ($finish->finish_time < $map->silver_time) {
                    $finish->finish_score = $map->silver_score;
                } elseif ($finish->finish_time < $map->bronze_time) {
                    $finish->finish_score = $map->bronze_score;
                } else {
                    $finish->finish_score = $map->finish_score;
                }

                FinishMapper::update()->execute($finish);
            }
        }
    }

    $lastId = \end($drivers)->id;
};
