<?php

include_once __DIR__ . '/phpOMS/Autoloader.php';
include_once __DIR__ . '/db.php';

use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\Message\Http\HttpRequest;

$request = HttpRequest::createFromSuperglobals();

$type = $request->getDataInt('type') ?? 1;
$map = '';
$offset = $request->getDataInt('offset') ?? 0;
$limit = $request->getDataInt('limit') ?? 500;
$uname = $request->getDataString('name') ?? '';

$endpoint = $request->getDataString('endpoint') ?? 'ranking';

// order
$order = $request->getDataString('order') ?? 'default';
if ($order !== 'finish' && $order !== 'at' && $order !== 'gold' && $order !== 'silver' && $order !== 'bronze' && $order !== 'time') {
    $order = 'default';
}

// user id
$uid = ($request->getData('uid') ?? '');

if (\preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $uid) !== 1
    || \strlen($uid) !== 36
) {
    $uid = '00000000-0000-0000-0000-000000000000';
}

$result = [];

if ($endpoint === 'types') {
    $query = new Builder($db);
    $query->raw('SELECT * FROM type;');

    $types = $query->execute()->fetchAll();

    foreach ($types as $type) {
        foreach ($type as $key => $var) {
            if (\is_numeric($key)) {
                continue;
            }

            $result[$type['type_id']][$key] = $var;
        }
    }
} elseif ($endpoint === 'maplist') {
    // get all maps for a type
    $query = new Builder($db);
    $query->raw(
        'SELECT map.*, COUNT(finish.finish_finish_time) AS fins
        FROM map
        LEFT JOIN finish ON map.map_nid = finish.finish_map
        LEFT JOIN type_map_rel ON map.map_uid = type_map_rel.type_map_rel_map
        LEFT JOIN driver ON finish.finish_driver = driver.driver_uid
        WHERE type_map_rel.type_map_rel_type = ' . ((int) $type) . '
        GROUP BY map.map_uid
        ORDER BY map.map_finish_score ASC, map.map_at_time ASC;'
    );
    $maps = $query->execute()->fetchAll();

    foreach ($maps as $map) {
        foreach ($map as $key => $var) {
            if (\is_numeric($key)) {
                continue;
            }

            $result[$map['map_uid']][$key] = $var;
        }
    }

    $query = new Builder($db);
    $query->raw(
        'SELECT map.map_uid, driver.driver_name, IFNULL(MIN(subquery.min_finish_time), 0) AS wr
        FROM map
        LEFT JOIN type_map_rel ON map.map_uid = type_map_rel.type_map_rel_map
        LEFT JOIN (
            SELECT finish_map, MIN(finish_finish_time) AS min_finish_time
            FROM finish
            GROUP BY finish_map
        ) AS subquery ON map.map_nid = subquery.finish_map
        LEFT JOIN finish ON map.map_nid = finish.finish_map AND subquery.min_finish_time = finish.finish_finish_time
        LEFT JOIN driver ON finish.finish_driver = driver.driver_uid
        WHERE type_map_rel.type_map_rel_type = ' . ((int) $type) . '
        GROUP BY map.map_uid, driver.driver_name;'
    );
    $temps = $query->execute()->fetchAll();

    foreach ($temps as $temp) {
        $result[$temp['map_uid']]['wr'] = $temp['wr'];
    }
} elseif ($endpoint === 'ranking') {
    // get ranking list
    $orderSql = 'score DESC, fins DESC, ats DESC, golds DESC, silvers DESC, bronzes DESC, ftime ASC';

    if ($order === 'default') {
        $orderSql = 'score DESC, fins DESC, ats DESC, golds DESC, silvers DESC, bronzes DESC, ftime ASC';
    } elseif ($order === 'finish') {
        $orderSql = 'fins DESC, score DESC, ats DESC, golds DESC, silvers DESC, bronzes DESC, ftime ASC';
    } elseif ($order === 'at') {
        $orderSql = 'ats DESC, score DESC, fins DESC, golds DESC, silvers DESC, bronzes DESC, ftime ASC';
    } elseif ($order === 'gold') {
        $orderSql = 'golds DESC, score DESC, fins DESC, ats DESC, silvers DESC, bronzes DESC, ftime ASC';
    } elseif ($order === 'silver') {
        $orderSql = 'silvers DESC, score DESC, fins DESC, ats DESC, golds DESC, bronzes DESC, ftime ASC';
    } elseif ($order === 'bronze') {
        $orderSql = 'bronzes DESC, score DESC, fins DESC, ats DESC, golds DESC, silvers DESC, ftime ASC';
    } elseif ($order === 'time') {
        $orderSql = 'ftime DESC, score DESC, fins DESC, ats DESC, golds DESC, silvers DESC, bronzes DESC';
    }

    $query = new Builder($db);
    $query->raw(
        'SELECT driver.driver_uid, driver.driver_name,
            SUM(finish.finish_finish_score) AS score,
            COUNT(finish.finish_finish_time) AS fins,
            COUNT(CASE WHEN finish.finish_finish_time <= map.map_at_time THEN finish.finish_finish_score ELSE NULL END) AS ats,
            COUNT(CASE WHEN finish.finish_finish_time <= map.map_gold_time THEN finish.finish_finish_score ELSE NULL END) AS golds,
            COUNT(CASE WHEN finish.finish_finish_time <= map.map_silver_time THEN finish.finish_finish_score ELSE NULL END) AS silvers,
            COUNT(CASE WHEN finish.finish_finish_time <= map.map_bronze_time THEN finish.finish_finish_score ELSE NULL END) AS bronzes,
            SUM(finish.finish_finish_time) AS ftime
        FROM driver
        JOIN finish ON driver.driver_uid = finish.finish_driver
        JOIN map ON finish.finish_map = map.map_nid
        JOIN type_map_rel ON map.map_uid = type_map_rel.type_map_rel_map
        WHERE type_map_rel.type_map_rel_type = ' . ((int) $type) . '
        GROUP BY driver.driver_uid
        ORDER BY ' . $orderSql . '
        LIMIT ' . $limit . '
        OFFSET ' . $offset . ';'
    );
    $scores = $query->execute()->fetchAll();

    $index = 0;
    foreach ($scores as $score) {
        ++$index;
        foreach ($score as $key => $var) {
            if (\is_numeric($key)) {
                continue;
            }

            $result[$score['driver_uid']][$key] = $var;
        }

        $result[$score['driver_uid']]['rank'] = $offset + $index;
    }
} elseif ($endpoint === 'userstats') {
    // get user stats
    $query = new Builder($db);
    $query->raw(
        'SELECT *, finish.finish_finish_time AS fins, finish.finish_finish_score AS score
        FROM map
        LEFT JOIN finish ON map.map_nid = finish.finish_map
        LEFT JOIN type_map_rel ON map.map_uid = type_map_rel.type_map_rel_map
        WHERE finish.finish_driver = \'' . $uid . '\'
            AND type_map_rel.type_map_rel_type = ' . ((int) $type) . '
        GROUP BY map.map_uid
        ORDER BY map.map_finish_score ASC, map.map_at_time ASC;'
    );
    $maps = $query->execute()->fetchAll();

    foreach ($maps as $map) {
        foreach ($map as $key => $var) {
            if (\is_numeric($key)) {
                continue;
            }

            $result[$map['map_uid']][$key] = $var;
        }
    }
} elseif ($endpoint === 'finduser') {
    // find user
    $drivers = DriverMapper::getAll()->where('name', '%' . $uname . '%', 'LIKE')->execute();

    foreach ($drivers as $driver) {
        $result[$driver->uid] = [
            'driver_uid' => $driver->uid,
            'driver_name' => $driver->name,
        ];
    }
} elseif ($endpoint === 'user') {
    $query = new Builder($db);
    $query->raw(
        'SELECT *
        (SELECT COUNT(*) + 1
     FROM (
         SELECT
             driver.driver_uid,
             SUM(finish.finish_finish_score) AS score
         FROM driver
         JOIN finish ON driver.driver_uid = finish.finish_driver
         JOIN map ON finish.finish_map = map.map_nid
         JOIN type_map_rel ON map.map_uid = type_map_rel.type_map_rel_map
         WHERE type_map_rel.type_map_rel_type = 1
         GROUP BY driver.driver_uid
         HAVING SUM(finish.finish_finish_score) > score
     ) AS subquery
    ) AS rank
        FROM driver
        WHERE driver.driver_uid = \'' . $uid . '\';'
    );
    $users = $query->execute()->fetchAll();

    foreach ($users as $user) {
        foreach ($user as $key => $var) {
            if (\is_numeric($key)) {
                continue;
            }

            $result[$user['driver_uid']][$key] = $var;
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
echo \json_encode($result, \JSON_PRETTY_PRINT);

$filePath = __DIR__ . '/stats/api.json';
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
