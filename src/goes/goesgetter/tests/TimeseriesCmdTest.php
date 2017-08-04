<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 1/18/16
 * Time: 5:56 PM
 */

namespace SierraHydrog\Goes;


class TimeseriesCmdTest extends \PHPUnit_Framework_TestCase
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

    public function testTimeseriesList()
    {
        global $commands;

        $args = ['timeseries', 'list'];
        $json = $commands->process($args);
        $this->assertNotNull($json);

        $ts = json_decode($json, true);
        $this->assertGreaterThan(100, count($ts));
    }

    public function testTimseriesDescription()
    {
        global $commands;

        $args = ['timeseries', 'location', "NFW"];
        $json = $commands->process($args);
        $this->assertNotNull($json);

        $ts = json_decode($json, true);
        $this->assertGreaterThan(100, count($ts));
    }

}
