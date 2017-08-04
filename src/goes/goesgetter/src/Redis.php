<?php

namespace SierraHydrog\Goes;

use Predis;

class Redis {
	private static $_instance;

    private $client = null;
    private $keys = [];

	private function __constructor() {
		parent::__construct();
    }

    /**
     * @return mixed
     */
	public static function getInstance() {
		global $config;

		if (Redis::$_instance != null) {
			return Redis::$_instance;
		}
        Redis::$_instance = new Redis();
        $i = Redis::$_instance;

        $i->client = new \Predis\Client('tcp://localhost');
        return Redis::$_instance;
    }

    public static function getClient() {
        $red = Redis::getInstance();
        return $red->client;
    }


}

