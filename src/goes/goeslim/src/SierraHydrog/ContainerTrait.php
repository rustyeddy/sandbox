<?php
namespace SierraHydrog;

trait ContainerTrait {

    /*
     * GET:
     *   - logger
     *   - renderer
     *   - errorHandler
     *   - db
     *   - cache
     *   - siteManager
     *   - dataStreamer
     *   - parser
     */
    public function __call($name, $args) {
        global $container;
        $log = $container['logger'];
        // $log->debug("Looking for dependency: $name");

        $c = $container[$name];
        if ($c !== null) {
            return $c;
        }
        $log->error("Could not find container for: " . $name);
        return NULL;
    }
}