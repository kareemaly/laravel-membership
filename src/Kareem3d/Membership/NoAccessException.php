<?php namespace Kareem3d\Membership;

class NoAccessException extends \Exception {

    /**
     * @param string $message
     */
    public function __construct($message = '')
    {
        if($message == '')
        {
            $message = 'You don\'t have permissions to access this page.';
        }

        parent::__construct($message, 403);
    }

}