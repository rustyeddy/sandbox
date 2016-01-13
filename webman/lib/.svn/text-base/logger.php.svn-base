<?php

$lpo_log_name = 'lpo.log';
$fp = null;

function lpo_print_r($foo)
{
    $o = print_r($foo, true);
    lpo_logit($o);
}

function lpo_logit( $str )
{
    global $lpo_log_name;
    
    $fp = fopen(LPO_LIB_DIR . $lpo_log_name, 'a+');
    
    $d = date("Y-m-d h:i:s");
    fwrite($fp, "--- $d ---\n$str \n\n");
    $fp = null;
}

function lpo_pre($var)
{
    $html = '<pre>';
    $html .= print_r($var, true);
    $html .= '</pre>';

    lpo_logit( $html );
}

?>
