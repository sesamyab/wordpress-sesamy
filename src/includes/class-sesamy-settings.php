<?php

class Sesamy_Settings {



	public function register_settings() {
		register_setting( 'sesamy', 'sesamy_lock_mode' );
		register_setting( 'sesamy', 'sesamy_content_types' );
		register_setting( 'sesamy', 'sesamy_client_id' );
	}


	public function get_content_types() {
		return get_option( 'sesamy_content_types', array() );
	}


	/**
	 * Get the public key
	 */
	public function get_public_settings() {

		return array(
			'content_types' => $this->get_content_types(),
		);
	}

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
	 */
	public function sesamy_settings_ep( $request ) {

		return new WP_REST_Response( $this->get_public_settings() );
	}

}
