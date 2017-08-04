<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 2/10/16
 * Time: 8:13 AM
 */

namespace SierraHydrog\Goes;


class MeasurementTest extends \PHPUnit_Framework_TestCase
{
    public function testTimeIncrement()
    {
        $ts = "2016-12-31 23:40:24";
        $c = new \Carbon\Carbon($ts);

        $c->addMinutes(15);

        $d = $c->toDateTimeString();
        $this->assertEquals($d, "2016-12-31 23:55:24");

        $c->addMinutes(15);
        $d = $c->toDateTimeString();
        $this->assertEquals($d, "2017-01-01 00:10:24");
    }
}
