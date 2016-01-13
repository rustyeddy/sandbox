<?php
function br2($s = '') {
    br($s . "<br/>\n");
}
function br($s = '') {
    $html =  $s . "<br/>\n";
    return $html;
}

function hr()
{
    $html =  "<hr/>";
    return $html;
}

function p($s = '') {
    $html =  '<p>' . $s . "</p>\n";
    return $html;
}

function href_blank( $anchor, $link, $caption = null )
{
    // TODO: add the captions, sadly I just don't use em.
    $html = "<a href='" . $link . "' target='_blank'>" . $anchor . "</a>";
    return $html;
}

function href( $anchor, $link, $caption = null )
{
    // TODO: add the captions, sadly I just don't use em.
    $html = "<a href='" . $link . "'>" . $anchor . "</a>";
    return $html;
}

function h2( $txt )
{
    $html =  "<h2>$txt</h2>\n";
    return $html;
}

function td($str, $class='')
{
    if ($class !== '') {
        $html =  "<td class='" . $class . "'>$str</td>\n";
    } else {
        $html =  "<td>$str</td>";
    }
    return $html;
}

function th($str, $class='')
{
    if ($class !== '') {
        $html =  "<th class='" . $class . "'>$str</td>\n";
    } else {
        $html =  "<th>$str</td>";
    }
    return $html;
}

function dump($foo)
{
    $html =  "<pre>";
    $html = print_r ($foo, 1);
    $html =  "</pre>";
    return $html;
}


?>
