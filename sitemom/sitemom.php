<?php

/**
 * Site Mom
 *
 * This plugin is used to track and help all of the websites that I manage.  It 
 *
 * @link              http://LakeParkOnline.com/Webmaster 
 * @since             1.0.0
 * @package           Site Mome
 *
 * @wordpress-plugin
 * Plugin Name:       Site Mom
 * Plugin URI:        http://lakeparkonline.com/sitemom/
 * Description:       This plugin ties together maintanance and a bunch of other stuff that goes into managing a website and clients.
 * Version:           0.0.1
 * Author:            Rusty Eddy
 * Author URI:        http://RustyEddy.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-name
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-sitemom-activator.php';

/**
 * The code that runs during plugin deactivation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-sitemom-deactivator.php';

/** This action is documented in includes/class-plugin-name-activator.php */
register_activation_hook( __FILE__, array( 'Sitemom_Activator', 'activate' ) );

/** This action is documented in includes/class-plugin-name-deactivator.php */
register_deactivation_hook( __FILE__, array( 'Sitemom_Deactivator', 'deactivate' ) );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-sitemom.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sitemom() {

	$plugin = new Sitemom();
	$plugin->run();

}
run_sitemom();
