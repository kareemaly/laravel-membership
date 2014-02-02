<?php namespace Kareem3d\Membership;


class RoleNotFoundException extends \Exception {

    /**
     * @param string $role
     */
    public function __construct($role)
    {
        parent::__construct("Sorry, you need to have `$role` permissions to access this.", 403);
    }
}