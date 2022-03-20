<?php
$options = getopt('', ["bootstrap:"]);

// require Kirby
require $options['bootstrap'] ?? __DIR__ . '/starterkit/kirby/bootstrap.php';

use Kirby\Cms\App as Kirby;
use Kirby\Database\Db;

$kirby = (new Kirby())->render();
$users = Db::select('users');
$result = [];
foreach( $users as $user) {
    $result[] = $user->email;
}
dump( $result);