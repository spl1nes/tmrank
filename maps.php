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

$temp = MapTypeMapper::getAll()->execute();
$types = [];
foreach ($temp as $t) {
    $types[$t->name] = $t;
}

// load csv
$row = 0;
if (($handle = \fopen(__DIR__ . '/maps.csv', 'r')) !== false) {
    while (($data = \fgetcsv($handle, 4096, ',')) !== false) {
        ++$row;

        if ($row === 1) {
            continue;
        }

        if (!isset($types[$data[1]])) {
            $type = new MapType();
            $type->name = $data[1];

            MapTypeMapper::create()->execute($type);

            $types[$data[1]] = $type;
        }

        // @var Map $map
        $map = MapMapper::get()->where('uid', $data[0])->execute();
        if ($map->id === 0) {
            $map = new Map();
            $map->uid = $data[0];
            $map->finish_score = $data[2];
            $map->bronze_score = $data[3];
            $map->silver_score = $data[4];
            $map->gold_score = $data[5];
            $map->at_score = $data[6];

            $mapInfoRequest = new HttpRequest(new HttpUri('https://prod.trackmania.core.nadeo.online/maps/?mapUidList=' . $map->uid));
            $mapInfoRequest->header->set('Content-Type', 'application/json');
            $mapInfoRequest->header->set('Authorization', 'nadeo_v1 t=' . \trim($authResponse2->data['accessToken'] ?? ''));
            $mapInfoRequest->header->set('User-Agent', 'Map pack ranking / ' . $email);
            $mapInfoRequest->data['audience'] = 'NadeoServices';
            $mapInfoRequest->setMethod('GET');
            $mapInfoResponse = Rest::request($mapInfoRequest);

            if ($mapInfoResponse->header->status !== 200) {
                echo "Invalid map response for " . $map->uid . "\n";

                $authResponse2 = authenticate($email, $password);

                $mapInfoRequest = new HttpRequest(new HttpUri('https://prod.trackmania.core.nadeo.online/maps/?mapUidList=' . $map->uid));
                $mapInfoRequest->header->set('Content-Type', 'application/json');
                $mapInfoRequest->header->set('Authorization', 'nadeo_v1 t=' . \trim($authResponse2->data['accessToken'] ?? ''));
                $mapInfoRequest->header->set('User-Agent', 'Map pack ranking / ' . $email);
                $mapInfoRequest->data['audience'] = 'NadeoServices';
                $mapInfoRequest->setMethod('GET');
                $mapInfoResponse = Rest::request($mapInfoRequest);

                if ($mapInfoResponse->header->status !== 200) {
                    \sleep(1);

                    continue;
                }
            }

            $map->nid = $mapInfoResponse->data[0]['mapId'];
            $map->name = $mapInfoResponse->data[0]['name'];
            $map->img = $mapInfoResponse->data[0]['thumbnailUrl'];

            $map->bronze_time = $mapInfoResponse->data[0]['bronzeScore'];
            $map->silver_time = $mapInfoResponse->data[0]['silverScore'];
            $map->gold_time = $mapInfoResponse->data[0]['goldScore'];
            $map->at_time = $mapInfoResponse->data[0]['authorScore'];

            MapMapper::create()->execute($map);
        } else {
            $map->finish_score = $data[2];
            $map->bronze_score = $data[3];
            $map->silver_score = $data[4];
            $map->gold_score = $data[5];
            $map->at_score = $data[6];

            MapMapper::update()->execute($map);
        }

        $rel = MapTypeRelMapper::get()
            ->where('map', $map->uid)
            ->where('type', $types[$data[1]]->id)
            ->execute();

        if ($rel->id === 0) {
            $relation = new MapTypeRel();
            $relation->map = $map->uid;
            $relation->type = $types[$data[1]]->id;

            MapTypeRelMapper::create()->execute($relation);
        }
    }

    \fclose($handle);
}
