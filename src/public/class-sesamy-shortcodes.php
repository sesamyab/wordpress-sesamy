<?php

/**
 * Define the shortcodes
 *
 *
 * @link       https://www.viggeby.com
 * @since      1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * Define the shortcodes
 *
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
	 * @since    1.0.0
	 */
	public function register() {

		$this->register_shortcode( 'sesamy_content_container' );
		$this->register_shortcode( 'sesamy_button_container' );
		$this->register_shortcode( 'sesamy_button' );
		$this->register_shortcode( 'sesamy_login' );
		$this->register_shortcode( 'sesamy_user_profile' );
		
	}
	
	public function register_shortcode($name) {

		add_shortcode( $name, [$this, $name]);
	}

	/**
	 * Remove empty values and convert array into html attributes 
	 */
	function html_attributes(array $array) {
		
		return implode(' ', array_map(function ($key, $value) {
			if (is_array($value)) {
				$value = implode(' ', $value);
			}
	
			return str_replace( '_', '-', $key ) . '="' . htmlspecialchars($value) . '"';
		}, array_keys($array), $array));
	}

	/*
	 * Remove empty values to avoid creating empty attributes
	 */
	function remove_empty_values(array $array) {
		return array_filter($array, function($value) {
			return !empty($value);
		});
	}

	/**
	 * Generate tag, empty content will generate a self-closing tag
	 */
	function make_tag( $name, $atts, $content){

		$a = $this->html_attributes($this->remove_empty_values($atts));

		$tag = "<$name" . (!empty($a) ? " $a" : "");
		$tag .= empty($content) ? "/>" : ">$content</$name>";

		return $tag;
	}

	public function sesamy_content_container($atts, $content){
	
		$atts = shortcode_atts( array(
			'show_childs_count' => '',
			'gradient' => ''
		), $atts, 'sesamy_content_container' );


		return $this->make_tag( 'sesamy-content-container', $atts, $content );
	}

	public function sesamy_button_container($atts, $content){
	
		$atts = shortcode_atts( array(
			'item_src' => '',
			'description ' => ''
		), $atts, 'sesamy_button_container' );

		return $this->make_tag( 'sesamy-button-container', $atts, $content );
	}

	public function sesamy_button($atts, $content){
	
		$atts = shortcode_atts( array(
			// Add here if needed
		), $atts, 'sesamy_button' );

		return $this->make_tag( 'sesamy-button', $atts, $content );
	}

	public function sesamy_login($atts, $content){
	
		$atts = shortcode_atts( array(
			'client_id' => ''
		), $atts, 'sesamy_login' );

		return $this->make_tag( 'sesamy-login', $atts, $content );
	}

	public function sesamy_user_profile($atts, $content){
	
		$atts = shortcode_atts( array(
			// Add here if needed
		), $atts, 'sesamy-login' );

		return $this->make_tag( 'sesamy-user-profile', $atts, $content );
	}

}
