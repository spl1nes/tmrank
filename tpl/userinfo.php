<?php
use phpOMS\DataStorage\Database\Query\Builder;

$uid = ($request->getData('user') ?? '');

if (\preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $uid) !== 1
    || \strlen($uid) !== 36
) {
    $uid = '00000000-0000-0000-0000-000000000000';
}

$types = MapTypeMapper::getAll()->execute();
$count = [];

    $result = [
        'driver_uid' => $uid,
        'driver_name' => '',
        'types' => []
    ];
    
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
            WHERE driver_uid = \'' . $uid . '\';'
        );
        $users = $query->execute()->fetchAll();

	$query = new Builder($db);
	$query->raw(
	    'SELECT count(*)
	    FROM type_map_rel
	    WHERE type_map_rel.type_map_rel_type = ' . ((int) $type->id) . ';'
	);
	$count[$type->id] = $query->execute()->fetchAll();
        
        $result['types'][$type->id] = [];

        foreach ($users as $user) {
            $result['driver_name'] = $user['driver_name'];
            
            $result['types'][$type->id] = [
                'type_id' => $type->id,
                'type_name' => $type->name,
                'score' => $user['score'],
                'fins' => $user['fins'],
                'ats' => $user['ats'],
                'golds' => $user['golds'],
                'silvers' => $user['silvers'],
                'bronzes' => $user['bronzes'],
                'ftime' => $user['ftime'],
                'rank' => $user['rank'],
            ];
        }
    }
?>

<div id="search_top" class="floater">
    <a class="button" href="?type=<?= (int) $current_type; ?>">Ranking</a>

    <form method="GET" action="/">
        <input type="text" name="user_search" placeholder="player name">
        <input type="hidden" name="page" value="user_search">
        <input type="submit" value="Search">
    </form>
</div>

<div class="floater">
    <table id="maps">
        <thead>
            <tr>
                <th>Type</th>
                <th>Rank</th>
                <th>Points</th>
                <th>Fins</th>
                <th>AT</th>
                <th>Gold</th>
                <th>Silver</th>
                <th>Bronze</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($result['types'] as $type) : 
		if (empty($type)) {continue;} 
                $type['ftime'] = (int) ($type['ftime'] / 1000);
                $days = (int) \floor($type['ftime'] / 86400);
                $hours = (int) \floor(($type['ftime'] % 86400) / 3600);
                $minutes = (int) \floor(($type['ftime'] % 3600) / 60);
                $seconds = $type['ftime'] % 60;
	    ?>
            <tr>
                <td><a href="?type=<?= $type['type_id']; ?>&page=user&uid=<?= $uid; ?>"><?= $type['type_name']; ?></a>
                <td><?= $type['rank']; ?></td>
                <td><?= $type['score']; ?></td>
                <td><?= $type['fins']; ?> / (<?= $count[$type['type_id']][0][0]; ?>)</td>
                <td><?= $type['ats']; ?></td>
                <td><?= $type['golds']; ?></td>
                <td><?= $type['silvers']; ?></td>
                <td><?= $type['bronzes']; ?></td>
                <td><?= \sprintf("%02d:%02d:%02d:%02d", $days, $hours, $minutes, $seconds); ?></td>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
