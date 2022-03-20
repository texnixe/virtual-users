<?php

namespace texnixe\dbusers;

use Kirby\Cms\App as Kirby;
use Kirby\Database\Db;

class CustomKirby extends Kirby
{


    public function users()
    {
        // get cached users if available
        if ($this->users !== null) {
            return $this->users;
        }

        $dbUsers        = [];
        $contentTable   = option('cookbook.dbusers.contentTable');
        // get users from database table
        $users          = Db::select(option('cookbook.dbusers.userTable'));
        $languageCode   = $this->multilang() === true ? $this->language()->code() : option('cookbook.dbusers.defaultLanguage');

        // loop through the users collection
        foreach ($users as $user) {
            $data            = $user->toArray();
            $content         = Db::first($contentTable, '*', ['id' => $user->id(), 'language' => $languageCode]);
            $data['content'] = $content !== false ? $content->toArray() : [];

            // for multi-language sites, add the translations to the translations array
            if ($this->multilang() === true) {
                unset($data['content']);
                $data['translations'] = $this->getDbContentTranslations($contentTable, $user->id());
            }
            // append data to the `$dbUsers` array
            $dbUsers[] = $data;
        }

        return $this->users = DbUsers::factory($dbUsers);
    }

    /**
     * Build content translations array
     *
     * @param string $table
     * @param string $id
     * @return array
     */
    protected function getDbContentTranslations(string $table, string $id): array
    {
        $translations = [];
        foreach ($this->languages() as $language) {
            $content =  Db::first($table, '*', ['id' => $id, 'language' => $language->code()]);
            if ($language === $this->defaultLanguage()) {
                $translations[] = [
                    'code'    => $language->code(),
                    'content' => $content !== false ? $content->toArray() : [],
                ];
            } else {
                $translations[] =  [
                    'code'    => $language->code(),
                    'content' => $content !== false ? $content->toArray() : [],
                ];
            }
        }

        return $translations;
    }
}