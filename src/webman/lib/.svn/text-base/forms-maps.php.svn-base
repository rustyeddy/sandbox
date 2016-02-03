<?php

require_once( LPO_LIB_DIR . 'forms.php' );
require_once( LPO_LIB_DIR . 'logger.php' );

  /*
   * The form mapper takes a gform id as a the array index, which
   * then maps that forms field id's into our human values.
   *
   * This may have to be hand coded for each new form, also when
   * that form changes.
   */
class LPO_Form_Map
{
    public $id;			// Gravity forms ID
    public $name;		// Human consumable name (form title)
    public $map = array();	// map of index values and field names

    function __construct($id, $name, $fields = array())
    {
	$this->id = $id;
	$this->name = $name;

	if ( count ( $fields ) > 0 ) {
	    $this->add_fields( $fields );
	}
    }

    function __init()
    {
	// Post data default fields
	$this->map[ 'id' ]		= 'entry_id';
	$this->map[ 'form_id' ]		= 'form_id';
	$this->map[ 'create_by' ]	= 'wp_user_id';
	$this->map[ 'date_created' ]	= 'date_created';
    }

    function add_fields( $fields )
    {
	foreach ( $fld as $idx => $property ) {

	    $this->map[ $idx ] = $property;

	}
    }

    function get_field_name( $name )
    {

	
    }

    function get_field_idx( $idx )
    {
	$fld = null;
	
	if ( array_key_exists( $this->map, $idx ) ) {
	     $fld = $this->fields[ $idx ];
	}

	return $fld;
    }
}

/*
class LPO_Form_Mapper
{
    static public $forms	= array();

    function __construct( $form = null )
    {
	if ( $form != null ) {
	    $this->add_form( $form );
	}
    }

    function add_form( $form )
    {
	$this->forms[ $form->id ] = $form;
    }

    function get_form( $id )
    {
	return $this->forms[ $id ];
    }

    function get_form_field( $id, $idx )
    {
	return $this->forms[ $id ]->get_field_idx( $idx );
    }
}
*/

if ( ! isset( $lpo_forms_map ) ) {
    global $lpo_forms_map;
    $lpo_forms_map = array();
}


 /**
  * Long Website Info Form
  */
$map = new LPO_Form_Map( 11, 'Site Long Form' );
$map->map = array(
    1  => '_lpo_has_domain',
    2  => '_lpo_domain',
    6  => '_lpo_has_dropbox',
    7  => '_lpo_dropbox_email',
    8  => '_lpo_theme_provider',
    9  => '_lpo_theme_name',

    10 => 'title',
    11 => 'description',

    15 => '_lpo_categories',
    16 => '_lpo_pages',
    17 => '_lpo_navbar',
    18 => '_lpo_menu_items',
    19 => '_lpo_logo_file',

    '21.1' => '_lpo_site_user:first_name',	// use current user defaults
    '21.2' => '_lpo_site_user:last_name',
    22 => '_lpo_site_user:email',
    23 => '_lpo_site_user:website',
    24 => '_lpo_site_user:password',
    25 => '_lpo_site_user:username',
    26 => '_lpo_site_user:role',
    
    27 => '_lpo_keywords',

    29 => '_lpo_competition',
    );

$lpo_forms_map[ $map->id ] = $map;

/*
 * Map the $entry[fields] from gravity forms entry to
 * LakeParkSites post_meta.
 *
 * This structure will need to be updated if the form
 * changes.
 *
 * Form ID 9 is the post_title already handled by the post
 * From ID 10 is the post_exerpt
 */
$map = new LPO_Form_Map( 12, 'Site Info Form' );
$map->map = array(

    1	=> '_lpo_has_domain',
    2	=> '_lpo_domain',

    6	=> '_lpo_has_dropbox',
    7	=> '_lpo_dropbox_account',

    8	=> '_lpo_theme_provider',
    9	=> '_lpo_theme_name',

    12	=> '_lpo_competition',

    /*
    10 => post_title
    11 => post_exerpt
    */

    );
$lpo_forms_map[ $map->id ] = $map;

/*
 * Now create the map for the style map
 */
$map = new LPO_Form_Map( 14, 'Site Style Form' );
$map->map = array(

    1  => '_lpo_theme_provider',
    2  => '_lpo_theme_name',
    3  => '_lpo_colors',
    4  => '_lpo_fonts',
    );

$lpo_forms_map[ $map->id ] = $map;

?>