<?php

namespace texnixe\dbusers;

use Kirby\Cms\Form;
use Kirby\Cms\User;
use Kirby\Database\Db;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Http\Idn;

class DbUser extends User
{

    /**
     * Create new user in database
     *
     * @param array|null $props
     * @return DbUser|mixed
     * @throws LogicException
     * @throws PermissionException
     */
    public static function create(array $props = null)
    {
        $data = $props;

        if (isset($props['email']) === true) {
            $data['email'] = Idn::decodeEmail($props['email']);
        }

        if (isset($props['password']) === true) {
            $data['password'] = User::hashPassword($props['password']);
        }

        $props['role'] = $props['model'] = strtolower($props['role'] ?? 'default');

        $user = static::factory($data);

        // create a form for the user
        $form = Form::for($user, [
            'values' => $props['content'] ?? []
        ]);
        // inject the content
        $user = $user->clone(['content' => $form->strings(true)]);
        // run the hook
        return $user->commit('create', ['user' => $user, 'input' => $props], function ($user, $props) {

            $data = [
                'id'       => $user->id(),
                'email'    => $user->email(),
                'language' => $user->language(),
                'name'     => $user->name()->value(),
                'role'     => $user->role()->id(),
                'password' => $user->password()
            ];

            // get language code for the content language
            if ($user->kirby()->multilang() === true) {
                $languageCode = $user->kirby()->defaultLanguage()->code();
            } else {
                $languageCode = $user->kirby()->option('cookbook.dbusers.defaultLanguage');
            }

            $result = Db::insert(option('cookbook.dbusers.userTable'), $data);
            if ($result === false) {
                throw new LogicException('The user could not be created');
            }

            //write content data to content table
            $user->writeDbContent($user->content()->toArray(), $user->id(), $languageCode);

            return $user;
        });
    }

    /**
     * Delete a user
     *
     * @return bool
     * @throws LogicException|PermissionException
     */
    public function delete(): bool
    {
        return $this->commit('delete', ['user' => $this], function ($user) {

            if ($user->exists() === false) {
                return true;
            }

            // delete the user from users table
            $bool = Db::delete(option('cookbook.dbusers.userTable'), ['id' => $user->id()]);
            if ($bool !== true) {
                throw new LogicException('The user "' . $user->email() . '" could not be deleted');
            }
            // delete content from all languages
            $user->deleteContentRows();
            // remove the user from users collection
            $user->kirby()->users()->remove($user);

            return true;
        });
    }

    /**
     * Delete all user-related content rows
     */
    protected function deleteContentRows(): bool
    {
        return Db::delete(option('cookbook.dbusers.contentTable'), ['id' => $this->id()]);
    }

    /**
     * Checks if the user exists in database table
     *
     * @return bool
     */
    public function exists(): bool
    {
        return (bool) Db::first(option('cookbook.dbusers.userTable'), '*', ['id' => $this->id()]);
    }

    /**
     * Update user data
     *
     * @param array|null $input
     * @param string|null $languageCode
     * @param bool $validate
     * @return static
     * @throws LogicException
     */
    public function update(array $input = null, string $languageCode = null, bool $validate = false): DbUser
    {
        // set language code to default language for non-multilang sites
        if ($languageCode === null) {
            $languageCode = option('cookbook.dbusers.defaultLanguage');
        }

        $result = $this->updateTable($input, $languageCode);
        if ($result !== true) {
            throw new LogicException('The user could not be updated');
        }

        // set auth user data only if the current user is this user
        if ($this->kirby()->users()->findBy('id', $this->id())->isLoggedIn() === true) {
            $this->kirby()->auth()->setUser($this);
        }

        return $this;
    }

    /**
     * Update credentials
     */
    protected function updateCredentials(array $credentials): bool
    {
        return Db::update(
            option('cookbook.dbusers.userTable'),
            array_merge(
                $this->credentials(),
                $credentials
            ),
            ['id' => $this->id()]
        );
    }

    /**
     * Update content table
     */
    protected function updateTable(array $data, string $languageCode = null): bool
    {
        $data['id']       = $this->id();
        $data['language'] = $languageCode;
        $contentTable     = option('cookbook.dbusers.contentTable');

        if (Db::first($contentTable, '*', ['id' => $this->id(), 'language' => $languageCode])) {
            $result = Db::update($contentTable, $data, ['id' => $this->id(), 'language' => $languageCode]);
        } else {
            $result = Db::insert($contentTable, $data);
        }

        return $result;
    }

    /**
     * Write data to content table
     */
    protected function writeDbContent(array $data, $id, string $languageCode = null)
    {
        $data['id']       = $id;
        $data['language'] = $languageCode;

        return Db::insert(option('cookbook.dbusers.contentTable'), $data);
    }

    /**
     * Write user password
     */
    protected function writePassword(string $password = null): bool
    {
        return Db::update(
            option('cookbook.dbusers.userTable'),
            ['password' => $password],
            ['id' => $this->id()]
        );
    }
}