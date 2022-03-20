<?php
/**
 * Call this script with absolute path to Kirby bootstrap file and name of sqlite file you want to create
 * e.g.
 * `php create-content-table.php --bootstrap="/Users/myname/sites/starterkit/kirby/bootstrap.php" --dbname="kirby-users.sqlite"
 */
$options = getopt('', ["dbname:", "bootstrap:"]);

// require Kirby
require $options['bootstrap'] ?? __DIR__ . '/starterkit/kirby/bootstrap.php';

use Kirby\Cms\App as Kirby;
use Kirby\Database\Database;

$kirby = (new Kirby())->render();

$dbFile =  kirby()->root('index') . '/' . ($options['dbname'] ?? 'kirby-users.sqlite');
$db     = new Database([
    'type'     => 'sqlite',
    'database' => $dbFile,
]);
// only create new table if it doesn't exist
if($db->validateTable('content') === false) {
    $result = $db->createTable('content', [
        'id' => [
            'type' => 'text',
            'unique' => false,
        ],
        'language' => [
            'type' => 'text',
        ],
        'company' =>  [
            'type' => 'text',
        ],
        'street' =>  [
            'type' => 'text',
        ],
        'zip' =>  [
            'type' => 'text',
        ],
        'city' =>  [
            'type' => 'text',
        ],
        'country' =>  [
            'type' => 'text',
        ],
        'bio' =>  [
            'type' => 'text',
        ],
        'website' =>  [
            'type' => 'text',
        ],
        'twitter' =>  [
            'type' => 'text',
        ],
        'instagram' =>  [
            'type' => 'text',
        ],
    ]);
    echo $result ? 'The table was successfully created' : 'An error occurred';
} else {
    echo 'The table already exists';
}
