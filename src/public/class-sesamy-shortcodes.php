<?php
/**
 * Define the shortcodes
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * Define the shortcodes
 *
 * @since      1.0.0
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Shortcodes {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 * @package    Sesamy
	 */
	public function register() {

		$this->register_shortcode( 'sesamy_content_container' );
		$this->register_shortcode( 'sesamy_button_container' );
		$this->register_shortcode( 'sesamy_button' );
		$this->register_shortcode( 'sesamy_login' );
		$this->register_shortcode( 'sesamy_profile' );
		$this->register_shortcode( 'sesamy_config' );
		$this->register_shortcode( 'sesamy_paywall_wizard' );
	}

	/**
	 * Register Shortcode
	 *
	 * @since 1.0.0
	 * @package    Sesamy
	 * @param string $name Shortcode name.
	 */
	public function register_shortcode( $name ) {

		// By convention the shortcodes have a corresponding public function with prefix get_.
		add_shortcode( $name, 'get_' . $name );
	}
}
