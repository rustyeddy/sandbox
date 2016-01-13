<?php

class configTest extends PHPUnit_Framework_TestCase
{
    /**
     * TODO: write a test config file
     */
    public function testRead()
    {
        global $config;
        $this->assertNotNull( $config );

        //print_r ( $config );
    }
}


?>