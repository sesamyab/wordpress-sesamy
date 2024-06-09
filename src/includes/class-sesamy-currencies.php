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

		// TODO: https://assets.sesamy.com/markets.json is not automatically updated from the catalog anymore.
		// Need to find a new way to get currencies. For now we just return a static array.
		$currencies = array(
			'EUR' => 'EUR',
			'DKK' => 'DKK',
			'NOK' => 'NOK',
			'SEK' => 'SEK',
			'GBP' => 'GBP',
		);

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
