<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 2/29/16
 * Time: 12:00 PM
 */

namespace SierraHydrog\Goes\Commands;


class ProcessCmdTest extends \PHPUnit_Framework_TestCase
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

    public function testStore()
    {
        global $commands;
    }

}
