<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 6/8/16
 * Time: 9:51 PM
 */

namespace SierraHydrog\Goes\Commands;


use SierraHydrog\Goes\StreamFactory;

class StreamCmd extends CommandBase
{
    // A constructor
    public function __construct()
    {
        $this->command_name = "stream";
        $this->helpstr = "process files from DCSToolkit load into AQUARIUS";

        $this->commands = [
            'fetch' => [
                'usage' => "fetch data from LRGS [file=fname]",
                'help' => 'fetch data using DCSTOOL kit or from file',
                'proc' => 'fetchStream',
            ],
            'parse' => [
                'usage' => 'goes stream parse',
                'help' => 'parse cached stream data',
                'proc' => 'parseData',
            ],
            'report' => [
                'usage'=> "goes stream report",
                'help' => 'report on stream data in cache',
                'proc' => 'reportStream',
            ],
            'store' => [
                'usage'=> 'goes stream store [aquarius=true] [db=true]',
                'help' => 'store data from cache to aquarius and/or local db',
                'proc' => 'storeMeasurements',
            ],
        ];
    }

    /**
     * Fetch a stream from the LRGS ground system (or a file) block up the messages and cache
     * them in redis.
     *
     * @param $args
     * @param $params
     */
    public function fetchStream($args, $params) {
        $fname = array_key_exists('file', $params) ?
            $params['file'] : null;
                
        $streamer = StreamFactory::getInstance();
        if ($fname) {
            $streamer->getStreamFile($fname);
        } else {
            $streamer->getStreamData();
        }
    }

    /**
     * Read stream blocks from the cache and parse them.
     */
    public function parseData($args, $params) {
        $streamer = StreamFactory::getInstance();
        $streamer->parseRawData();
    }

    /**
     * @param $args
     * @param $params
     */
    public function storeMeasurements($args, $params) {
        $streamer = new StreamFactory();
        $streamer->storeMeasurements();
    }

    public function reportStream($args, $params) {
        
    }

}