<?php

use phpOMS\DataStorage\Database\Query\Builder;

$types = MapTypeMapper::getAll()->execute();

$offset = $request->getDataInt('offset') ?? 0;
$limit  = 500;

$query = new Builder($db);
$query->raw(
    'SELECT COUNT(map.map_id) AS maps
    from map
    JOIN type_map_rel ON map.map_uid = type_map_rel.type_map_rel_map
    WHERE type_map_rel.type_map_rel_type = ' . ((int) $current_type)
);
$maps = $query->execute()->fetchAll();

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
    WHERE type_map_rel.type_map_rel_type = ' . ((int) $current_type) . '
    GROUP BY driver.driver_uid
    ORDER BY ' . $orderSql . '
    LIMIT ' . $limit . '
    OFFSET ' . $offset . ';'
);
$scores = $query->execute()->fetchAll();
?>
<div id="ranking_top" class="floater">
    <select id="type_selector">
        <option disabled>Select</option>
        <?php foreach ($types as $type) : ?>
        <option value="<?= $type->id; ?>"<?= $current_type === $type->id ? ' selected' : ''; ?>><?= \htmlspecialchars($type->name); ?></option>
        <?php endforeach; ?>
    </select>

    <a class="button" href="?type=<?= (int) $current_type; ?>&page=maps">Maps</a>

    <form method="GET" action="/">
        <input type="text" name="user_search" placeholder="player name">
        <input type="hidden" name="page" value="user_search">
        <input type="submit" value="Search">
    </form>

    <span class="global_maps_stat">Maps: <?= $maps[0][0]; ?></span>
</div>
<div class="floater">
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Name</th>
                <th><label for="sortPoints">Points</label><input type="radio" id="sortPoints" value="default" name="sort"<?= $order === 'default' ? ' checked' : ''; ?>> <span>🠋</span></th>
                <th><label for="sortFins">Fins</label><input type="radio" id="sortFins" value="finish" name="sort"<?= $order === 'finish' ? ' checked' : ''; ?>> <span>🠋</span></th>
                <th><label for="sortAts">AT</label><input type="radio" id="sortAts" value="at" name="sort"<?= $order === 'at' ? ' checked' : ''; ?>> <span>🠋</span></th>
                <th><label for="sortGolds">Gold</label><input type="radio" id="sortGolds" value="gold" name="sort"<?= $order === 'gold' ? ' checked' : ''; ?>> <span>🠋</span></th>
                <th><label for="sortSilvers">Silver</label><input type="radio" id="sortSilvers" value="silver" name="sort"<?= $order === 'silver' ? ' checked' : ''; ?>> <span>🠋</span></th>
                <th><label for="sortBronzes">Bronze</label><input type="radio" id="sortBronzes" value="bronze" name="sort"<?= $order === 'bronze' ? ' checked' : ''; ?>> <span>🠋</span></th>
                <th><label for="sortTimes">Time</label><input type="radio" id="sortTimes" value="time" name="sort"<?= $order === 'time' ? ' checked' : ''; ?>> <span>🠉</span></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = $offset;
            foreach ($scores as $score) : ++$i;

            $score[8] = (int) ($score[8] / 1000);
            $days = (int) \floor($score[8] / 86400);
            $hours = (int) \floor(($score[8] % 86400) / 3600);
            $minutes = (int) \floor(($score[8] % 3600) / 60);
            $seconds = $score[8] % 60;
            ?>
            <tr>
                <td><?= $i; ?></td>
                <td><a href="?page=user&type=<?= $current_type; ?>&uid=<?= $score[0]; ?>"><?= \htmlspecialchars($score[1]); ?></a></td>
                <td><?= $score[2]; ?></td>
                <td><?= $score[3]; ?></td>
                <td><?= $score[4]; ?></td>
                <td><?= $score[5]; ?></td>
                <td><?= $score[6]; ?></td>
                <td><?= $score[7]; ?></td>
                <td><?= \sprintf("%02d:%02d:%02d:%02d", $days, $hours, $minutes, $seconds); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="floater center">
    <a class="button" href="?type=<?= $current_type ?>&offset=<?= \max(0, $offset - $limit); ?>&order=<?= $order; ?>"><</a>
    <a class="button" href="?type=<?= $current_type ?>&offset=<?= $offset + $limit; ?>&order=<?= $order; ?>">></a>
</div>

