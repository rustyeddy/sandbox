<?php

namespace SierraHydrog\Goes;

// This should represent an DAL Interface
class MongoDAL {

	// This is a singleton
	private static $instance = null;

    // Single get an instance
	static function getInstance() {
		if (null === static::$instance) {
			static::$instance = new MongoDAL();
            static::$instance->authenticate();
		}
		return static::$instance;
	}

}
