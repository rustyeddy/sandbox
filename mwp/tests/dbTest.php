<?php

class dbTest extends PHPUnit_Framework_TestCase
{
    private $_db = null;

    /**
     * get the DB class. A meta class for our mysql database
     */
    private function _getdb()
    {
        if ( $this->_db == null ) {
            $this->_db = new DB();
        }
        return $this->_db;
    }

    public function testConnect()
    {
        $db = $this->_getdb();

        $this->assertNotNull( $db );
        
        $mysql = $db->get_mysql();
        $this->assertNotNull ( $mysql->is_connected() );

        $this->_db = $db;
        return $db;
    }

    /**
     * @depends testConnect
     */
    public function testDatabaseList( $db )
    {
        $this->assertNotNull( $db );
        
        $mysql = $db->get_mysql();
        $this->assertNotNull ( $mysql );
        $this->assertTrue ( $mysql->is_connected() );

        $dbs = $db->get_list();
        $this->assertTrue( count ( $dbs ) > 2 );

        return $dbs;
    }

    /**
     * This function test database creation from Database::create()
     * method.  Not the DB::create() method!
     */
    public function testDatabaseCreate( )
    {
        $db = $this->_getdb();
        
        $dbname = 'wp_phpunit' . rand( 0, 9999 ); /* Randomize? */
        $mysql = $db->get_mysql();

        $ret = $db->create( $dbname, $mysql->dbuser, $mysql->dbpasswd, $mysql->dbhost );
        $this->assertNotFalse( $ret );

        // Now get a list of databases and make sure our database has been created
        $dblist = $db->get_list();

        $dbidx = array_search( $dbname, $dblist );
        $this->assertNotFalse( $dbidx );

        return $dbname;
    }


    /**
     * @depends testDatabaseCreate
     */
    public function testDatabaseDelete( $dbname )
    {
        $db = $this->_getdb();
        $ret = $db->delete( $dbname );
        $this->assertNotFalse( $ret );

        // Now lets try and find it in our list
        $dblist = $db->get_list();
        $idx = array_search ( $dbname, $dblist );

        $this->assertFalse( $idx );
    }

}



?>