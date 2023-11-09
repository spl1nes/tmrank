<?php

use phpOMS\DataStorage\Database\Query\Builder;

$types = MapTypeMapper::getAll()->execute();

$uid = ($request->getData('uid') ?? '');

if (\preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $uid) !== 1
    || \strlen($uid) !== 36
) {
    $uid = '00000000-0000-0000-0000-000000000000';
}

$query = new Builder($db);
$query->raw(
    'SELECT *, finish.finish_finish_time AS fins, finish.finish_finish_score AS score
    FROM map
    LEFT JOIN finish ON map.map_nid = finish.finish_map
    LEFT JOIN type_map_rel ON map.map_uid = type_map_rel.type_map_rel_map
    WHERE finish.finish_driver = \'' . $uid . '\'
        AND type_map_rel.type_map_rel_type = ' . ((int) $current_type) . '
    GROUP BY map.map_uid
    ORDER BY map.map_finish_score ASC, map.map_at_time ASC;'
);
$maps = $query->execute()->fetchAll();

$query = new Builder($db);
$query->raw(
    'SELECT *
    FROM map
    LEFT JOIN type_map_rel ON map.map_uid = type_map_rel.type_map_rel_map
    LEFT JOIN finish ON map.map_nid = finish.finish_map
        AND finish.finish_driver = \'' . $uid . '\'
        AND type_map_rel.type_map_rel_type = ' . ((int) $current_type) . '
    WHERE finish.finish_map IS NULL
        AND type_map_rel.type_map_rel_type = ' . ((int) $current_type) . '
    GROUP BY map.map_uid
    ORDER BY map.map_finish_score ASC, map.map_at_time ASC;'
);
$missing = $query->execute()->fetchAll();

$user = DriverMapper::get()->where('uid', $uid)->execute();
?>
<div id="ranking_top" class="floater">
    <select id="type_selector" aria-label="Map types">
        <option disabled>Select</option>
        <?php foreach ($types as $type) : ?>
        <option value="<?= $type->id; ?>"<?= $current_type === $type->id ? ' selected' : ''; ?>><?= \htmlspecialchars($type->name); ?></option>
        <?php endforeach; ?>
    </select>

    <a class="button" href="?type=<?= (int) $current_type; ?>">Ranking</a>

    <form method="GET" action="/">
        <input type="text" name="user_search" placeholder="player name">
        <input type="hidden" name="page" value="user_search">
        <input type="submit" value="Search">
    </form>

    <span class="global_maps_stat"><?= \htmlspecialchars($user->name); ?> Maps: <?=\count($maps); ?> / <?= \count($maps) + \count($missing) ?></span> 
</div>

<div class="floater">
    <h1>Unfinished</h1>
    <table id="maps">
        <thead>
            <tr>
                <th>Name</th>
                <th>ID</th>
                <th>Points</th>
                <th>AT</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($missing as $map) :
                $ms = (int) ($map['map_at_time'] % 1000);
                $map['map_at_time'] = (int) ($map['map_at_time'] / 1000);
                $days = (int) \floor($map['map_at_time'] / 86400);
                $hours = (int) \floor(($map['map_at_time'] % 86400) / 3600);
                $minutes = (int) \floor(($map['map_at_time'] % 3600) / 60);
                $seconds = $map['map_at_time'] % 60;
            ?>
            <tr>
                <td>
                    <a href="https://trackmania.io/#/rooms/leaderboard/<?= \htmlspecialchars($map['map_uid']); ?>"><?= \preg_replace('/(\$...)/', '', \str_replace(['$o', '$i', '$w', '$n', '$t', '$s', '$g', '$z'], '', \htmlspecialchars($map['map_name']))); ?></a>
                    <div class="img-container"><img loading="lazy" width="400px" src="<?= $map['map_img']; ?>"></div>
                </td>
                <td><?= \htmlspecialchars($map['map_uid']); ?></td>
                <td><?= $map['map_finish_score']; ?>/<?= $map['map_bronze_score']; ?>/<?= $map['map_silver_score']; ?>/<?= $map['map_gold_score']; ?>/<?= $map['map_at_score']; ?></td>
                <td><?= \sprintf("%02dh %02dm %02ds.%03d", $hours, $minutes, $seconds, $ms); ?></td>
            <?php endforeach; ?>
    </table>
</div>

<div class="floater">
    <h1>Finished</h1>
    <table id="maps">
        <thead>
            <tr>
                <th>Name</th>
                <th>ID</th>
                <th>Points</th>
                <th>AT</th>
                <th>Fin</th>
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

                $fms = (int) ($map['fins'] % 1000);
                $map['fins'] = (int) ($map['fins'] / 1000);
                $fdays = (int) \floor($map['fins'] / 86400);
                $fhours = (int) \floor(($map['fins'] % 86400) / 3600);
                $fminutes = (int) \floor(($map['fins'] % 3600) / 60);
                $fseconds = $map['fins'] % 60;
            ?>
            <tr>
                <td>
                    <a href="https://trackmania.io/#/rooms/leaderboard/<?= \htmlspecialchars($map['map_uid']); ?>"><?= \preg_replace('/(\$...)/', '', \str_replace(['$o', '$i', '$w', '$n', '$t', '$s', '$g', '$z'], '', \htmlspecialchars($map['map_name']))); ?></a>
                    <div class="img-container"><img loading="lazy" width="400px" src="<?= $map['map_img']; ?>"></div>
                </td>
                <td><?= \htmlspecialchars($map['map_uid']); ?></td>
                <td><?php if($map['map_finish_score'] === $map['map_bronze_score'] && $map['map_bronze_score'] === $map['map_silver_score'] && $map['map_silver_score'] === $map['map_gold_score'] && $map['map_gold_score'] = $map['map_at_score']) : ?>
                        <strong><?= $map['score'] ?></strong>
                    <?php else : ?>
                        <?= ($map['score'] === $map['map_finish_score'] ? '<strong>' : '') . $map['map_finish_score'] . ($map['score'] === $map['map_finish_score'] ? '</strong>' : ''); ?>/<?= ($map['score'] === $map['map_bronze_score'] ? '<strong>' : '') . $map['map_bronze_score'] . ($map['score'] === $map['map_bronze_score'] ? '</strong>' : ''); ?>/<?= ($map['score'] === $map['map_silver_score'] ? '<strong>' : '') . $map['map_silver_score'] . ($map['score'] === $map['map_silver_score'] ? '</strong>' : ''); ?>/<?= ($map['score'] === $map['map_gold_score'] ? '<strong>' : '') . $map['map_gold_score'] . ($map['score'] === $map['map_gold_score'] ? '</strong>' : ''); ?>/<?= ($map['score'] === $map['map_at_score'] ? '<strong>' : '') . $map['map_at_score'] . ($map['score'] === $map['map_at_score'] ? '</strong>' : ''); ?>
                    <?php endif; ?>
                </td>
                <td><?= if ($hours > 0) : \sprintf("%02dh %02dm %02ds.%03d", $hours, $minutes, $seconds, $ms); elseif ($minutes > 0) : \sprintf("%02dm %02ds.%03d", $minutes, $seconds, $ms); else : \sprintf("%02ds.%03d", $minutes, $seconds, $ms); endif; ?></td>
                <td><?= \sprintf("%02dh %02dm %02ds.%03d", $fhours, $fminutes, $fseconds, $fms); ?></td>
            <?php endforeach; ?>
    </table>
</div>
