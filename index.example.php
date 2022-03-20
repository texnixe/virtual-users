<?php

require __DIR__ . '/kirby/bootstrap.php';
require __DIR__ . '/site/plugins/users-from-database/classes/CustomKirby.php';

use texnixe\dbusers\CustomKirby;

echo (new CustomKirby())->render();