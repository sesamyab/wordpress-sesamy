<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.viggeby.com
 * @since      1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Set default endpoint to debug
		if( get_option( 'sesamy_api_endpoint' ) === false ) {
			add_option( 'sesamy_api_endpoint', 'develop' );
		}
		
	}

}
