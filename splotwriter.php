<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://cog.dog/
 * @since             1.0.0
 * @package           Splotwriter
 *
 * @wordpress-plugin
 * Plugin Name:       SPLOT Writer
 * Plugin URI:        https://github.com/cogdog/splotwriter
 * Description:       Provides the same functionality of the TRU Writer SPLOT for any theme; quite experimental, bugs might be rampant.
 * Version:           1.1.0
 * Author:            Alan Levine
 * Author URI:        https://cog.dog/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       splotwriter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SPLOTWRITER_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-splotwriter-activator.php
 */
function activate_splotwriter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-splotwriter-activator.php';
	Splotwriter_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-splotwriter-deactivator.php
 */
function deactivate_splotwriter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-splotwriter-deactivator.php';
	Splotwriter_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_splotwriter' );
register_deactivation_hook( __FILE__, 'deactivate_splotwriter' );

require plugin_dir_path( __FILE__ ) . 'includes/splot-tools.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-splotwriter.php';

// class for adding admin options
require plugin_dir_path( __FILE__ ) . 'includes/class-splot-plugins.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_splotwriter() {

	$plugin = new Splotwriter();
	$plugin->run();

}
run_splotwriter();
