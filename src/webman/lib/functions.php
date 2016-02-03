<?php

  /*
   * In the future use the css class to provide more flexibility for styling
   */
function webman_table_from_array( $ar, $css = null )
{
    $html = "<div class='webman-table-wrap'>";
    $html .= "  <table class='webman-table'>";

    foreach ( $ar as $k => $v ) {

	$html .= "<tr class='webman-table-row'>";
	$html .= "    <td class='webman-table-cell webman-table-key'>" . $k . "</td>";
	$html .= "    <td class='webman-table-cell webman-table-value'>" . $v . "</td>";
	$html .= "</tr>";
    }

    $html .= "  </table>";
    $html .= "</div>";

    return $html;
}


?>