<?php

namespace texnixe\dbusers;

use Kirby\Cms\Users;

class DbUsers extends Users
{
    public function create($data)
    {
        return DbUser::create($data);
    }

    public static function factory(array $users, array $inject = [])
    {
        $collection = new static();

        // read all user blueprints
        foreach ($users as $props) {
            $user = DbUser::factory($props + $inject);
            $collection->set($user->id(), $user);
        }

        return $collection;
    }
}