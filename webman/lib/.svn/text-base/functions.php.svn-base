<?php

  /*
   * In the future use the css class to provide more flexibility for styling
   */
function lpo_table_from_array( $ar, $css = null )
{
    $html = "<div class='lpo-table-wrap'>";
    $html .= "  <table class='lpo-table'>";

    foreach ( $ar as $k => $v ) {

	$html .= "<tr class='lpo-table-row'>";
	$html .= "    <td class='lpo-table-cell lpo-table-key'>" . $k . "</td>";
	$html .= "    <td class='lpo-table-cell lpo-table-value'>" . $v . "</td>";
	$html .= "</tr>";
    }

    $html .= "  </table>";
    $html .= "</div>";

    return $html;
}


?>