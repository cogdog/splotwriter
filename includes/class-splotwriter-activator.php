<?php

/**
 * Fired during plugin activation
 *
 * @link       https://cog.dog/
 * @since      1.0.0
 *
 * @package    Splotwriter
 * @subpackage Splotwriter/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Splotwriter
 * @subpackage Splotwriter/includes
 * @author     Alan Levine <cogdogblog@gmail.com>
 */
class Splotwriter_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		splotwriter_setup();
	}
}