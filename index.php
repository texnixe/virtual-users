<?php

namespace texnixe\dbusers;

load([
    'texnixe\\dbusers\\dbuser'  => 'classes/DbUser.php',
    'texnixe\\dbusers\\dbusers' => 'classes/DbUsers.php',
], __DIR__);

use Kirby\Cms\App as Kirby;

Kirby::plugin('cookbook/dbusers', [
    'blueprints' => [
        'users/admin' => __DIR__ . '/blueprints/users/admin.yml',
    ],
        // add options to be more flexible regarding table names
        'options' => [
            'contentTable'    => 'content',
            'userTable'       => 'users',
            'defaultLanguage' => 'en',
        ],
]);