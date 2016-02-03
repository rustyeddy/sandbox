<?php

class scanTest extends PHPUnit_Framework_TestCase
{
    public function testScanner()
    {
        global $scanner;

        $this->assertNotNull( $scanner );
        $sites = $scanner->look_for_sites( );
        $this->assertGreaterThan( 1, count ( $sites ) );

        return $sites;
    }

    /**
     * @depends testScanner
     */
    public function testSitesWrite( $sites )
    {
        global $scanner;

        $ret = $scanner->save_sites( $sites );

        $this->assertGreaterThan( 0, $ret );
        $this->assertFileExists( $scanner->sitesfile );
    }

    public function testSitesRead()
    {
        global $scanner;
        $nsites = $scanner->read_sites_file();
        $this->assertGreaterThan( 0, $nsites );
    }
}

