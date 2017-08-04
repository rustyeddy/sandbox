<?php
namespace SierraHydrog\Goes;


class LocationCmdTest extends \PHPUnit_Framework_TestCase
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

    public function testLocationList()
    {
        global $commands;

        $args = ['location', 'list'];
        $json = $commands->process($args);
        $this->assertNotNull($json);

        $locs = json_decode($json, true);
        $this->assertGreaterThan(100, count($locs));
    }
}
