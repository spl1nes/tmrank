<?php

include __DIR__ . '/phpOMS/Autoloader.php';
include __DIR__ . '/db.php';
include __DIR__ . '/config.php';

// load csv
$row = 0;
if (($handle = \fopen(__DIR__ . '/remove.csv', 'r')) !== false) {
    while (($data = \fgetcsv($handle, 4096, ',')) !== false) {
        ++$row;

        if ($row === 1) {
            continue;
        }

        $rel = MapTypeRelMapper::get()->where('uid', $data[0])->execute();
        if ($rel->id !== 0) {
            MapTypeRelMapper::delete()->execute($rel);
        }

        $rel = MapTypeRelMapper::get()->where('uid', $data[0])->execute();
        if ($rel->id === 0) {
            $map = MapMapper::get()->where('uid', $data[0])->execute();
            MapMapper::delete()->execute($map);

            $fins = FinishMapper::getAll()->where('map', $map->nid)->execute();
            foreach ($fins as $fin) {
                FinishMapper::delete()->execute($fin);
            }
        }
    }
}

