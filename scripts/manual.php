<?php

include __DIR__ . '/../phpOMS/Autoloader.php';
include __DIR__ . '/../db.php';
include __DIR__ . '/../config.php';

use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\Rest;
use phpOMS\Uri\HttpUri;

// load csv
$row = 0;
if (($handle = \fopen(__DIR__ . '/manual.csv', 'r')) !== false) {
    while (($data = \fgetcsv($handle, 4096, ',')) !== false) {
        ++$row;

        if ($row === 1) {
            continue;
        }

        // @var Map $map
        $map = MapMapper::get()->where('uid', $data[1])->execute();
        if ($map->id === 0) {
            continue;
        }

        // @var Driver $driver
        $driver = DriverMapper::get()->where('uid', $data[0])->execute();
        if ($driver->id === 0) {
            continue;
        }

        $finish = FinishMapper::get()
            ->where('map', $map->nid)
            ->where('driver', $driver->uid)
            ->execute();

        if ($finish->id === 0) {
            $finish = new Finish();
            $finish->driver = $driver->uid;
            $finish->map = $map->nid;
            $finish->finish_time = (int) $data[2];

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
        } elseif ($finish->finish_time !== ((int) $data[2])) {
            $finish->finish_time = (int) $data[2];

            $score = 0;
            if ($finish->finish_time < $map->at_time) {
                $score = $map->at_score;
            } elseif ($finish->finish_time < $map->gold_time) {
                $score = $map->gold_score;
            } elseif ($finish->finish_time < $map->silver_time) {
                $score = $map->silver_score;
            } elseif ($finish->finish_time < $map->bronze_time) {
                $score = $map->bronze_score;
            } else {
                $score = $map->finish_score;
            }

            if ($finish->finish_score <= $score) {
                $finish->finish_score = $score;
                FinishMapper::update()->execute($finish);
            }
        }
    }
}
