<?php
/**
 * Sesamy Settings
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * This class is for Sesamy Settings age.
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Settings {
	/**
	 * Register settings
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 */
	public function register_settings() {
		register_setting( 'sesamy', 'sesamy_lock_mode' );
		register_setting( 'sesamy', 'sesamy_global_currency' );
		register_setting( 'sesamy', 'sesamy_content_types' );
		register_setting( 'sesamy', 'sesamy_tags' );
		register_setting( 'sesamy', 'sesamy_client_id' );
		register_setting( 'sesamy', 'sesamy_paywall_url' );
		register_setting( 'sesamy', 'sesamy_paywall_wizard', 
		array(
			'type' => 'boolean',
      'sanitize_callback' => 'rest_sanitize_boolean',
      'default' => false, // Default value must be a boolean
		) );

	}

	/**
	 * Get Content types option
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 */
	public function get_content_types() {
		return get_option( 'sesamy_content_types', array() );
	}

	/**
	 * Get the public key
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 */
	public function get_public_settings() {

		return array(
			'content_types' => $this->get_content_types(),
			'paywall_url'   => get_option( 'sesamy_paywall_url', '' ),
		);
	}

	/**
	 * Register Rount settings
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 */
	public function register_route() {

		register_rest_route(
			'sesamy/v1',
			'/settings',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'sesamy_settings_ep' ),
				'permission_callback' => '__return_true',
				'args'                => array(),
			)
		);
	}


	/**
	 * Endpoint for returning currencies
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $request Request method call.
	 */
	public function sesamy_settings_ep( $request ) {

		return new WP_REST_Response( $this->get_public_settings() );
	}
}
