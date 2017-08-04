<?php

// Load the auto loader
//include $basedir . "autoload.php";

// Bootstrap
require_once "../" . "bootstrap.php";

// Make sure we have access to the config, commands and log objects
global $config;
global $commands;
global $log;

// Get the request URI for this run
$requri = $_SERVER['REQUEST_URI'];

// Break out the rest objects and treat them like CLI commands
$args = explode('/', $requri);

// The first element of array will always be null
array_shift($args);

// "route" URLs accordingly
switch ($args[0]) {

    // Prefixed with /api/ will return JSON (REST call)
    case 'api':
        header('Content-Type: application/json');
        $config['output'] = 'json';
        array_shift($args);

        // Process commands according to the args.
        $output = $commands->process($args);
        echo $output;

        break;

    // Prefixed with /doc/ will return processed markdown
    case 'doc':
        // TODO: Handle parsedown and doc dir.
        break;

    // Otherwise handle everything else as HTML if it exists.
    default:
        header('Content-Type: text/html');
        $config['output'] = 'html';
        if (! count($args) ) {
            $args[] = 'home';
        }

        $html = file_get_contents("header.html");
        $html .= file_get_contents("body.html");
        $html .= file_get_contents("footer.html");

        echo $html;
        break;
}


function handleMarkdown($args)
{
    global $docsdir;

    $pd = new Parsedown();
    $fname = $docsdir . 'intro.md';
    $text = file_get_contents($fname);
    if ($text === false) {
        echo "Could not read the file.";
    }
    $html = $pd->text($text);
    return $html;
}
