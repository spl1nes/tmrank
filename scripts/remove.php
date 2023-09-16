<?php

include __DIR__ . '/../phpOMS/Autoloader.php';
include __DIR__ . '/../db.php';
include __DIR__ . '/../config.php';

use phpOMS\DataStorage\Database\Query\Builder;

// load csv
$row = 0;
if (($handle = \fopen(__DIR__ . '/remove.csv', 'r')) !== false) {

    $temp = MapTypeMapper::getAll()->execute();
    $types = [];
    foreach ($temp as $t) {
        $types[$t->name] = $t;
    }

    while (($data = \fgetcsv($handle, 4096, ',')) !== false) {
        ++$row;

        if ($row === 1) {
            continue;
        }

        if (!isset($types[$data[1]])) {
            continue;
        }

        $rel = MapTypeRelMapper::get()
	    ->where('map', $data[0])
	    ->where('type', $types[$data[1]]->id)
	    ->execute();

        if ($rel->id !== 0) {
            MapTypeRelMapper::delete()->execute($rel);
        }

        $rel = MapTypeRelMapper::get()
	    ->where('map', $data[0])
	    ->limit(1)
	    ->execute();

        if ($rel->id === 0) {
            $map = MapMapper::get()->where('uid', $data[0])->execute();
            MapMapper::delete()->execute($map);

	    $delete = new Builder($db);
	    $delete->raw(
	        'DELETE FROM finish 
	        WHERE finish.finish_map = "' . $map->nid . '";'
	    )->execute();
        }
    }
}

// Driver cleanup
$delete = new Builder($db);
$delete->raw(
	'DELETE FROM DRIVER 
 	WHERE driver.driver_uid IN (
  		SELECT driver.driver_uid FROM driver 
    		LEFT JOIN finish 
      			on driver.driver_uid = finish.finish_driver 
	 	WHERE finish.finish_driver IS NULL
   	);'
)->execute();
