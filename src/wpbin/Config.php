<?php

require_once "outputs.php";

class Config
{
    private $configFile = "~/.sandbox.json";

    private $config;

    public function __construct()
    {
        global $config;

        if (!file_exists($this->configFile)) {
            fatal("Could not open config file");
        }

        $json = file($this->configFile);
        if (!$json) {
            fatal("Could not open file: " . $this->configFile);
        }

        $config = json_decode($json);
        if (!$config) {
            fatal("Could not decode json string" . json_last_error_msg());
        }
    }
}
