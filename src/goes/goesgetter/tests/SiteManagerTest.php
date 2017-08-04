<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 1/2/16
 * Time: 3:20 PM
 */

namespace SierraHydrog\Goes;


class SiteManagerTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        // copy the test data
        $res = shell_exec('tests/bin/setup-data');
    }

    public static function tearDownAfterClass()
    {
        // remove temporary test data
        $res = shell_exec('tests/bin/teardown-data');
    }

    public function testLoadSites()
    {
        $sm = SiteManager::getInstance();

        $bySiteId = $sm->getSites();
        $this->assertCount(11, array_keys($bySiteId));
        $this->assertArrayHasKey('NFW', $bySiteId);
    }

    public function testGetSiteById()
    {
        $sm = SiteManager::getInstance();

        $ID = "NFW";
        $site = $sm->getSite($ID);
        $this->assertInstanceOf("SierraHydrog\Goes\Site", $site);
        $this->assertEquals($site->getMnemonic(), $ID);
    }
}
