<?php

function output($str)
{
    print($str);
}

function fatal($str)
{
    output("ERROR: " . $str . "\n");
    exit(-1);
}
