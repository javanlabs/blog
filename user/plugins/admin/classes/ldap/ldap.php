<?php

namespace Grav\Plugin\Admin;

use Grav\Common\Grav;

class Ldap
{
    private $grav;

    private $ldap;

    private $bindStatus;

    private $url;

    private $adminDn;

    private $adminPassword;

    private $searchDn;

    private $filter;

    private $account;

    public function __construct(Grav $grav)
    {
        $this->url              = $grav['config']->get('plugins.admin.ldap.url');
        $this->adminDn          = $grav['config']->get('plugins.admin.ldap.admin_dn');
        $this->adminPassword    = $grav['config']->get('plugins.admin.ldap.admin_password');
        $this->searchDn         = $grav['config']->get('plugins.admin.ldap.search_dn');
        $this->filter           = $grav['config']->get('plugins.admin.ldap.filter');

        $ldap = @ldap_connect($this->url);

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = ldap_bind($ldap, $this->adminDn, $this->adminPassword);

        $this->ldap = $ldap;
        $this->bindStatus = $bind;
    }

    public function auth($username, $password)
    {
        if($this->bindStatus) {
            $filter = str_replace('{username}', $username, $this->filter);

            $result = ldap_search($this->ldap, $this->searchDn, $filter);

            $info = ldap_get_entries($this->ldap, $result);

            if(count($info) && $info['count'] == 1) {
                $rdn = $info[0]['dn'];

                if(@ldap_bind($this->ldap, $rdn, $password)) {
                    $info[0]['password_readable'] = $password;
                    $this->account = $info;

                    return true;
                }
            }

            return false;
        }
    }

    public function account()
    {
        return $this->account;
    }
}