<?php

$webman_log_name = 'webman.log';
$fp = null;

function webman_print_r($foo)
{
    $o = print_r($foo, true);
    webman_logit($o);
}

function webman_logit( $str )
{
    global $webman_log_name;
    
    $fp = fopen(WEBMAN_LIB_DIR . $webman_log_name, 'a+');
    
    $d = date("Y-m-d h:i:s");
    fwrite($fp, "--- $d ---\n$str \n\n");
    $fp = null;
}

function webman_pre($var)
{
    $html = '<pre>';
    $html .= print_r($var, true);
    $html .= '</pre>';

    webman_logit( $html );
}

?>
