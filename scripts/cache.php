<?php

include_once __DIR__ . '/../phpOMS/Autoloader.php';
include_once __DIR__ . '/../db.php';

use phpOMS\DataStorage\Database\Query\Builder;

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\DataStorage\Database\Connection\SQLiteConnection;
use phpOMS\DataStorage\Database\DatabaseStatus;

// DB connection
$db = new SQLiteConnection([
    'db' => 'sqlite',
    'database' => __DIR__ . '/tm_pack2.sqlite',
]);

$db->connect();

if ($db->getStatus() !== DatabaseStatus::OK) {
    exit;
}

DataMapperFactory::db($db);

$types = MapTypeMapper::getAll()->execute();

foreach (DriverMapper::yield()->execute() as $driver) {
    foreach ($types as $type) {
        $query = new Builder($db);
        $query->raw(
            'SELECT driver_uid, driver_name, score, fins, ats, golds, silvers, bronzes, ftime, rank
            FROM (
              SELECT
                driver.driver_uid,
                driver.driver_name,
                SUM(finish.finish_finish_score) AS score,
                COUNT(finish.finish_finish_time) AS fins,
                COUNT(CASE WHEN finish.finish_finish_time <= map.map_at_time THEN finish.finish_finish_score ELSE NULL END) AS ats,
                COUNT(CASE WHEN finish.finish_finish_time <= map.map_gold_time THEN finish.finish_finish_score ELSE NULL END) AS golds,
                COUNT(CASE WHEN finish.finish_finish_time <= map.map_silver_time THEN finish.finish_finish_score ELSE NULL END) AS silvers,
                COUNT(CASE WHEN finish.finish_finish_time <= map.map_bronze_time THEN finish.finish_finish_score ELSE NULL END) AS bronzes,
                SUM(finish.finish_finish_time) AS ftime,
                ROW_NUMBER() OVER (
                    ORDER BY SUM(finish.finish_finish_score) DESC, 
                    COUNT(finish.finish_finish_time) DESC,
                    COUNT(CASE WHEN finish.finish_finish_time <= map.map_at_time THEN finish.finish_finish_score ELSE NULL END) DESC,
                    COUNT(CASE WHEN finish.finish_finish_time <= map.map_gold_time THEN finish.finish_finish_score ELSE NULL END) DESC,
                    COUNT(CASE WHEN finish.finish_finish_time <= map.map_silver_time THEN finish.finish_finish_score ELSE NULL END) DESC,
                    COUNT(CASE WHEN finish.finish_finish_time <= map.map_bronze_time THEN finish.finish_finish_score ELSE NULL END) DESC,
                    SUM(finish.finish_finish_time) ASC
                ) AS rank
              FROM driver
              JOIN finish ON driver.driver_uid = finish.finish_driver
              JOIN map ON finish.finish_map = map.map_nid
              JOIN type_map_rel ON map.map_uid = type_map_rel.type_map_rel_map
              WHERE type_map_rel.type_map_rel_type = ' . $type->id . '
              GROUP BY driver.driver_uid, driver.driver_name
            ) AS RankedDrivers
            WHERE driver_uid = \'' . $driver->uid . '\';'
        );
        $stats = $query->execute()->fetchAll();
    
        foreach ($stats as $stat) {
            if ($stat['fins'] < 1) {
                continue;
            }
          
            $driverStat = DriverStatMapper::get()->where('uid', $uid)->where('type', $type->id)->execute();

            if ($driverStat->id === 0) {
                $driverStat = new DriverStat();
                $driverStat->uid = $driver->uid;
                $driverStat->type = $type->id;
            }

            $driverStat->score = $stat['score'];
            $driverStat->fins = $stat['fins'];
            $driverStat->ats = $stat['ats'];
            $driverStat->golds = $stat['golds'];
            $driverStat->silvers = $stat['silvers'];
            $driverStat->bronzes = $stat['bronzes'];
            $driverStat->ftime = $stat['ftime'];
            $driverStat->rank = $stat['rank'];

            if ($driverStat->id === 0) {
                DriverStatMapper::create()->execute($driverStat);
            } else {
                DriverStatMapper::update()->execute($driverStat);
            }
        }
    }
}
