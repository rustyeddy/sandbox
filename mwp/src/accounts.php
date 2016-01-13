<?php

function get_account( $username )
{

}

function get_account_by_dir( $dir )
{

}

class Account extends Command
{
    public $username;
    public $homedir;
    public $uid;
    public $gid;

    public function __construct( $path = '' ) {

    	$paths = explode( '/', $path );

        /** 
         * XXX - Create an account class and store all of this information
         * in the account class.
         */
        $userinfo = posix_getpwuid( fileowner( $path ) );
        $pwname = posix_getpwnam( $this->username );

        $this->username = $userinfo[ 'name' ];
        $this->uid = $pwname['uid'];
        $this->gid = $pwname['gid'];
        $this->homedir = $pwname['dir'];
    }
}

?>