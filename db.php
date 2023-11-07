<?php
include_once __DIR__ . '/phpOMS/Autoloader.php';

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\DataStorage\Database\Connection\SQLiteConnection;
use phpOMS\DataStorage\Database\DatabaseStatus;

class Driver
{
    public int $id = 0;
    public string $uid = '';
    public string $name = '';
    public int $last_name_check = 0;
}

class NullDriver extends Driver {}

class DriverMapper extends DataMapperFactory
{
    public const COLUMNS = [
        'driver_id'   => ['name' => 'driver_id',   'type' => 'int',    'internal' => 'id'],
        'driver_uid'  => ['name' => 'driver_uid',  'type' => 'string', 'internal' => 'uid'],
        'driver_name' => ['name' => 'driver_name', 'type' => 'string', 'internal' => 'name'],
        'driver_last_name_check' => ['name' => 'driver_last_name_check', 'type' => 'int', 'internal' => 'last_name_check'],
    ];

    public const TABLE = 'driver';
    public const PRIMARYFIELD = 'driver_id';
}

class DriverStat
{
    public int $id = 0;
    public int $type = 0;
    public string $uid = '';
    public int $score = 0;
    public int $fins = 0;
    public int $ats = 0;
    public int $golds = 0;
    public int $silvers = 0;
    public int $bronzes = 0;
    public int $ftime = 0;
    public int $rank = 0;
}

class NullDriverStat extends DriverStat {}

class DriverStatMapper extends DataMapperFactory
{
    public const COLUMNS = [
        'driverstat_id'   => ['name' => 'driverstat_id',   'type' => 'int',    'internal' => 'id'],
        'driverstat_type'  => ['name' => 'driverstat_type',  'type' => 'int', 'internal' => 'type'],
        'driverstat_uid'  => ['name' => 'driverstat_uid',  'type' => 'string', 'internal' => 'uid'],
        'driverstat_score'  => ['name' => 'driverstat_score',  'type' => 'int', 'internal' => 'score'],
        'driverstat_fins'  => ['name' => 'driverstat_fins',  'type' => 'int', 'internal' => 'fins'],
        'driverstat_ats'  => ['name' => 'driverstat_ats',  'type' => 'int', 'internal' => 'ats'],
        'driverstat_golds'  => ['name' => 'driverstat_golds',  'type' => 'int', 'internal' => 'golds'],
        'driverstat_silvers'  => ['name' => 'driverstat_silvers',  'type' => 'int', 'internal' => 'silvers'],
        'driverstat_bronzes'  => ['name' => 'driverstat_bronzes',  'type' => 'int', 'internal' => 'bronzes'],
        'driverstat_ftime'  => ['name' => 'driverstat_ftime',  'type' => 'int', 'internal' => 'ftime'],
        'driverstat_rank'  => ['name' => 'driverstat_rank',  'type' => 'int', 'internal' => 'rank'],
    ];

    public const TABLE = 'driverstat';
    public const PRIMARYFIELD = 'driverstat_id';
}

class MapType
{
    public int $id = 0;
    public string $name = '';
}

class NullMapType extends MapType {}

class MapTypeMapper extends DataMapperFactory
{
    public const COLUMNS = [
        'type_id'   => ['name' => 'type_id',   'type' => 'int',    'internal' => 'id'],
        'type_name' => ['name' => 'type_name', 'type' => 'string', 'internal' => 'name'],
    ];

    public const TABLE = 'type';
    public const PRIMARYFIELD = 'type_id';
}

class Map
{
    public int $id = 0;
    public string $nid = '';
    public string $uid = '';
    public string $name = '';
    public string $img = '';
    public int $finish_score = 0;
    public int $bronze_score = 0;
    public int $silver_score = 0;
    public int $gold_score = 0;
    public int $at_score = 0;
    public int $bronze_time = 0;
    public int $silver_time = 0;
    public int $gold_time = 0;
    public int $at_time = 0;
}

class NullMap extends Map {}

class MapMapper extends DataMapperFactory
{
    public const COLUMNS = [
        'map_id'   => ['name' => 'map_id',   'type' => 'int',    'internal' => 'id'],
        'map_nid'  => ['name' => 'map_nid',  'type' => 'string', 'internal' => 'nid'],
        'map_uid'  => ['name' => 'map_uid',  'type' => 'string', 'internal' => 'uid'],
        'map_name' => ['name' => 'map_name', 'type' => 'string', 'internal' => 'name'],
        'map_img' => ['name' => 'map_img', 'type' => 'string', 'internal' => 'img'],
        'map_finish_score' => ['name' => 'map_finish_score', 'type' => 'int', 'internal' => 'finish_score'],
        'map_bronze_score' => ['name' => 'map_bronze_score', 'type' => 'int', 'internal' => 'bronze_score'],
        'map_silver_score' => ['name' => 'map_silver_score', 'type' => 'int', 'internal' => 'silver_score'],
        'map_gold_score' => ['name' => 'map_gold_score', 'type' => 'int', 'internal' => 'gold_score'],
        'map_at_score' => ['name' => 'map_at_score', 'type' => 'int', 'internal' => 'at_score'],
        'map_bronze_time' => ['name' => 'map_bronze_time', 'type' => 'int', 'internal' => 'bronze_time'],
        'map_silver_time' => ['name' => 'map_silver_time', 'type' => 'int', 'internal' => 'silver_time'],
        'map_gold_time' => ['name' => 'map_gold_time', 'type' => 'int', 'internal' => 'gold_time'],
        'map_at_time' => ['name' => 'map_at_time', 'type' => 'int', 'internal' => 'at_time'],
    ];

    public const TABLE = 'map';
    public const PRIMARYFIELD = 'map_id';
}

class MapTypeRel
{
    public int $id = 0;
    public int $type = 0;
    public string $map = '';
}

class NullMapTypeRel extends MapTypeRel {}

class MapTypeRelMapper extends DataMapperFactory
{
    public const COLUMNS = [
        'type_map_rel_id'   => ['name' => 'type_map_rel_id',   'type' => 'int',    'internal' => 'id'],
        'type_map_rel_type' => ['name' => 'type_map_rel_type', 'type' => 'int', 'internal' => 'type'],
        'type_map_rel_map' => ['name' => 'type_map_rel_map', 'type' => 'string', 'internal' => 'map'],
    ];

    public const TABLE = 'type_map_rel';
    public const PRIMARYFIELD = 'type_map_rel_id';
}

class Finish
{
    public int $id = 0;
    public string $driver = '';
    public string $map = '';
    public int $finish_time = 0;
    public int $finish_score = 0;
}

class NullFinish extends Finish {}

class FinishMapper extends DataMapperFactory
{
    public const COLUMNS = [
        'finish_id'   => ['name' => 'finish_id',   'type' => 'int',    'internal' => 'id'],
        'finish_driver'  => ['name' => 'finish_driver',  'type' => 'string', 'internal' => 'driver'],
        'finish_map' => ['name' => 'finish_map', 'type' => 'string', 'internal' => 'map'],
        'finish_finish_time' => ['name' => 'finish_finish_time', 'type' => 'int', 'internal' => 'finish_time'],
        'finish_finish_score' => ['name' => 'finish_finish_score', 'type' => 'int', 'internal' => 'finish_score'],
    ];

    public const TABLE = 'finish';
    public const PRIMARYFIELD = 'finish_id';
}

// DB connection
$db = new SQLiteConnection([
    'db' => 'sqlite',
    'database' => __DIR__ . '/tm_pack.sqlite',
]);

$db->connect();

if ($db->getStatus() !== DatabaseStatus::OK) {
    exit;
}

DataMapperFactory::db($db);
