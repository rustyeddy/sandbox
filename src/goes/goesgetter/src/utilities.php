<?php

namespace SierraHydrog\Goes;

/**
 * Return the value of the given key if it exists.  Null or false
 * otherwise.
 *
 * @param $k key to be retrieved
 * @param $a array to be accessed
 * @return mixed value to be returned.
 */
function array_get_value($k, $a) {
    $val = false;
    if (array_key_exists($k, $a)) {
        $val = $a[$k];
    }
    return $val;
}


function decode_csv_file($fname) {
    $fh = fopen($fname, "r") or
          die("Could not open " . $fname);
    $header = null;
    $assoc = [];

    while (($data = fgetcsv($fh)) != false) {
        if (!$header) {
            $header = array_map('trim', $data);
            continue;
        }
        $assoc[] = array_combine($header, array_map('trim', $data));
    }
    return $assoc;
}