#!/usr/bin/env php

<?php

require_once "Config.php";

$prog = array_shift($argv);

function getdbh() {
    $dbh = new PDO("mysql:host=localhost", "root", "password");
    return $dbh;
}
$dbh = getdbh();

function splitArgs(&$args) {
    $params = [];
    $nargs = count($args);
    for ($i = 0; $i < $nargs; $i++) {
        $arg = $args[$i];

        $eq = strpos($arg, '=');
        if ($eq) {
            $data = explode('=', $arg);
            $params[$data[0]] = $data[1];
            unset($args[$i]);
        }
    }
    return $params;
}

$params = splitArgs($argv);
$cmd = array_shift($argv);

switch ($cmd) {
    case 'list':
        dblist();
        break;

    case 'create':
        dbcreate($argv, $params);
        break;

    default:
        error("Hmm: I don't know what to do with: " . $cmd);
}

function dblist()
{
    global $dbh;

    $dbs = $dbh->query("show databases");
    while (($db = $dbs->fetchColumn(0)) !== false) {
        print $db . "\n";
    }
}

function dbcreate($argv, $params)
{
    global $dbh;
    $user = $params['user'];
    $pass = $params['pass'];
    $dbname = $params['dbname'];

    try {
        $dbh->exec("create database `$dbname`; " .
            "create user '$user'@'localhost' identified by '$pass'; " .
            "grant all on `$dbname`.* TO '$user'@'localhost'; " .
            "flush privileges")
        or die(print_r($dbh->errorInfo(), true));
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
