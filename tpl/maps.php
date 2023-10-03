<?php

use phpOMS\DataStorage\Database\Query\Builder;

$types = MapTypeMapper::getAll()->execute();

$query = new Builder($db);
$query->raw(
    'SELECT map.*, COUNT(finish.finish_finish_time) AS fins
    FROM map
    LEFT JOIN finish ON map.map_nid = finish.finish_map
    LEFT JOIN type_map_rel ON map.map_uid = type_map_rel.type_map_rel_map
    LEFT JOIN driver ON finish.finish_driver = driver.driver_uid
    WHERE type_map_rel.type_map_rel_type = ' . ((int) $current_type) . '
    GROUP BY map.map_uid
    ORDER BY map.map_finish_score ASC, map.map_at_time ASC;'
);
$maps = $query->execute()->fetchAll();

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
    WHERE type_map_rel.type_map_rel_type = ' . ((int) $current_type) . '
    GROUP BY map.map_uid, driver.driver_name;'
);
$temps = $query->execute()->fetchAll();

$wrs = [];
foreach ($temps as $temp) {
    $wrs[$temp['map_uid']] = $temp;
}

?>
<div id="ranking_top" class="floater">
    <select id="type_selector">
        <option disabled>Select</option>
        <?php foreach ($types as $type) : ?>
        <option value="<?= $type->id; ?>"<?= $current_type === $type->id ? ' selected' : ''; ?>><?= \htmlspecialchars($type->name); ?></option>
        <?php endforeach; ?>
    </select>

    <form method="GET" action="/">
        <input type="text" name="user_search">
        <input type="hidden" name="page" value="user_search">
        <input type="submit" value="Search">
    </form>

    <a class="button" href="?type=<?= (int) $current_type; ?>">Ranking</a>

    <span class="global_maps_stat">Maps: <?= \count($maps); ?></span>
</div>
<div class="floater">
    <table id="maps">
        <thead>
            <tr>
                <th>Name</th>
                <th>ID</th>
                <th>Points</th>
                <th>AT</th>
                <th colspan="2">WR</th>
                <th>Fins</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($maps as $map) :
                $ms = (int) ($map['map_at_time'] % 1000);
                $map['map_at_time'] = (int) ($map['map_at_time'] / 1000);
                $days = (int) \floor($map['map_at_time'] / 86400);
                $hours = (int) \floor(($map['map_at_time'] % 86400) / 3600);
                $minutes = (int) \floor(($map['map_at_time'] % 3600) / 60);
                $seconds = $map['map_at_time'] % 60;
        
                $map['wr'] = $wrs[$map['map_uid']]['wr'];

                $wms = (int) ($map['wr'] % 1000);
                $map['wr'] = (int) ($map['wr'] / 1000);
                $wdays = (int) \floor($map['wr'] / 86400);
                $whours = (int) \floor(($map['wr'] % 86400) / 3600);
                $wminutes = (int) \floor(($map['wr'] % 3600) / 60);
                $wseconds = $map['wr'] % 60;
            ?>
            <tr>
                <td>
                    <a href="https://trackmania.io/#/rooms/leaderboard/<?= \htmlspecialchars($map['map_uid']); ?>">
                        <?= \preg_replace('/(\$...)/', '', \str_replace(['$o', '$i', '$w', '$n', '$t', '$s', '$g', '$z'], '', \htmlspecialchars($map['map_name']))); ?>
                    </a>
                    <div class="img-container"><img loading="lazy" width="400px" src="<?= $map['map_img']; ?>"></div>
                </td>
                <td><?= \htmlspecialchars($map['map_uid']); ?></td>
                <td><?= $map['map_finish_score']; ?>/<?= $map['map_bronze_score']; ?>/<?= $map['map_silver_score']; ?>/<?= $map['map_gold_score']; ?>/<?= $map['map_at_score']; ?></td>
                <td><?= \sprintf("%02d:%02d:%02d.%03d", $hours, $minutes, $seconds, $ms); ?></td>
                <td><?= \sprintf("%02d:%02d:%02d.%03d", $whours, $wminutes, $wseconds, $wms); ?></td>
                <td><?= \htmlspecialchars($wrs[$map['map_uid']]['driver_name']); ?></td>
                <td><?= $map['fins']; ?>
            <?php endforeach; ?>
    </table>
</div>
