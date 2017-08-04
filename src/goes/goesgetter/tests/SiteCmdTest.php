<?php
namespace SierraHydrog\Goes;


class SiteCmdTest extends \PHPUnit_Framework_TestCase
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

    public function testSiteList()
    {
        global $commands;
        global $config;

        $args = [ 'site', 'list' ];
        $json = $commands->process($args);
        $this->assertNotNull($json);

        $sites = json_decode($json, true);
        $this->assertCount(9, $sites);

        $this->assertArrayHasKey('NFW', $sites);
    }

    public function testSiteDescribe()
    {
        global $commands;
        $args = ['site', 'describe', 'NFW'];
        $json = $commands->process($args);
        $this->assertNotNull($json);

        $site = json_decode($json);
        $this->assertEquals('NFW', $site->mnemonic);
    }

    public function testTimeseriesStrings()
    {
        global $commands;
        $args = ['site', 'missing'];
        $json = $commands->process($args);
        $this->assertNotNull($json);

        $site = json_decode($json);
        $this->assertEquals('NFW', $site->mnemonic);
    }
}
