<?php

namespace Grav\Plugin\Admin;

use Grav\Common\File\CompiledYamlFile;
use Grav\Common\Grav;
use Grav\Common\User\User;

class LdapUser
{
    private $grav;

    private $name;

    private $username;

    private $email;

    private $password;

    public function __construct(Grav $grav, $data)
    {
        $this->grav = $grav;

        $user = $data[0];

        $this->name     = $user['sn'][0];
        $this->username = $user['uid'][0];
        $this->email    = $user['mail'][0];
        $this->password = $user['password_readable'];
    }

    public function exists()
    {
        $username = strtolower($this->username);
        $user = User::load($username);

        return $user->exists() ? true : false;
    }

    public function save()
    {
        $data = [
            'email'         => $this->email,
            'fullname'      => $this->name,
            'title'         => 'Team',
            'access'        => [
                'admin'     => ['login' => true, 'super' => true],
                'site'      => ['login' => true]
            ],
            'state'         => 'enabled',
            'password'      => $this->password
        ];

        $username = strtolower($this->username);

        // Create user object and save it
        $user = new User($data);
        $file = CompiledYamlFile::instance($this->grav['locator']->findResource('user://accounts/' . $username . YAML_EXT, true, true));
        $user->file($file);
        $user->save();
    }
}