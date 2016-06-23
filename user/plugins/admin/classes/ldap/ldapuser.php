<?php

namespace Grav\Plugin\Admin;

use Grav\Common\File\CompiledYamlFile;
use Grav\Common\Grav;
use Grav\Common\User\User;

class LdapUser
{
    private $grav;

    private $username;

    public function __construct(Grav $grav, $username)
    {
        $this->grav = $grav;

        $this->username = $username;
    }

    private function getData()
    {
        $username = strtolower($this->username);
        $file_path = $this->grav['locator']->findResource('user://accounts/' . $this->username . YAML_EXT, true, true);
        $file = CompiledYamlFile::instance($file_path);
        $data = $file->content();

        return $data;
    }

    public function isExist()
    {
        $data = $this->getData();

        return ! empty($data) ? true : false;
    }

    public function save($data)
    {
        $user = $data[0];

        $username = strtolower($user['uid'][0]);

        $data = [
            'email'         => $user['mail'][0],
            'fullname'      => $user['sn'][0],
            'title'         => 'Team',
            'access'        => [
                'admin'     => ['login' => true, 'super' => true],
                'site'      => ['login' => true]
            ],
            'state'         => 'enabled',
            'password'      => $user['password_readable']
        ];

        // Create user object and save it
        $user = new User($data);
        $file = CompiledYamlFile::instance($this->grav['locator']->findResource('user://accounts/' . $username . YAML_EXT, true, true));
        $user->file($file);
        $user->save();
    }

    public function updatePassword($password)
    {
        $data = $this->getData();

        unset($data['hashed_password']);

        $data['password'] = $password;

        $user = new User($data);
        $file = CompiledYamlFile::instance($this->grav['locator']->findResource('user://accounts/' . $this->username . YAML_EXT, true, true));
        $user->file($file);
        $user->save();
    }
}