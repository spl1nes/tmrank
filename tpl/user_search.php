<?php
    $drivers = DriverMapper::getAll()
        ->where('name', '%' . ($request->getDataString('user_search') ?? '') . '%', 'LIKE')
        ->limit(100)
        ->execute();
?>
<div id="search_top" class="floater">
    <a class="button" href="?type=<?= (int) $current_type; ?>">Back</a>

    <form method="GET" action="?page=user_search">
        <input type="text" name="user_search">
        <input type="hidden" name="page" value="user_search">
        <input type="submit" value="Search">
    </form>
</div>

<div class="floater">
    <table id="maps">
        <thead>
            <tr>
                <th>Name</th>
                <th>ID</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($drivers as $driver) : ?>
            <tr><td><a href="?page=userinfo&user=<?= $driver->uid; ?>"><?= $driver->name; ?></a></td><td><?= $driver->uid; ?></td>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
