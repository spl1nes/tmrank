<?php
$uid = ($request->getData('user') ?? '');

if (\preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $uid) !== 1
    || \strlen($uid) !== 36
) {
    $uid = '00000000-0000-0000-0000-000000000000';
}

$driver = DriverMapper::get()
    ->where('uid', $uid)
    ->execute();
?>


