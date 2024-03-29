<?php
/**
 * Seasamy currencies
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * This class Set API for get currencies
 *
 * @since      1.0.0
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Currencies {
	/**
	 * Get the public key.
	 *
	 * @since      1.0.0
	 * @package    Sesamy
	 */
	public static function get_currencies() {

		$currencies = get_transient( 'sesamy_currencies' );

		// Use transient to avoid calling api more than needed.
		if ( false === $currencies ) {

			$req  = wp_remote_get( Sesamy::$instance->get_assets_url() . '/markets.json' );
			$json = wp_remote_retrieve_body( $req );
			$data = json_decode( $json );

			$currencies = array();

			foreach ( $data as $item ) {
				$currencies[ $item->currency ] = $item->currency;
			}

			set_transient( 'sesamy_currencies', $currencies, 60 );

		}

		return $currencies;
	}

	/**
	 * Register route for currency.
	 *
	 * @since      1.0.0
	 * @package    Sesamy
	 */
	public function register_route() {

		register_rest_route(
			'sesamy/v1',
			'/currencies',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'sesamy_currencies_ep' ),
				'permission_callback' => '__return_true',
				'args'                => array(),
			)
		);
	}


	/**
	 * Endpoint for returning currencies
	 *
	 * @since      1.0.0
	 * @package    Sesamy
	 * @param array $request Request Method.
	 */
	public function sesamy_currencies_ep( $request ) {

		return new WP_REST_Response( self::get_currencies() );
	}
}
