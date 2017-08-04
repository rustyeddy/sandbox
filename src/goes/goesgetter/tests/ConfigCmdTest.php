<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 1/7/16
 * Time: 7:37 PM
 */

namespace SierraHydrog\Goes;


class ConfigCmdTest extends \PHPUnit_Framework_TestCase
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

    public function testConfigList()
    {
        global $config;
        global $commands;

        $args = ['config', 'list'];
        $json = $commands->process($args);
        $this->assertNotNull($json);

        $cfg = json_decode($json, true);
        $this->assertSameSize($config, $cfg);
    }
}
