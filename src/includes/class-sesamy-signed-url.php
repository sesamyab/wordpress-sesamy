<?php
/**
 * Sesamy Valid URL
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
/**
 * Sesamy Valid URL
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_JWT_Helper {
	/**
	 * True if the url is signed and valid for the post
	 *
	 * @param str $jwt JWT Token.
	 * @since  1.0.0
	 * @package    Sesamy
	 * @return boolean
	 */
	public function verify( $jwt ) {

		// Get the public key from sesamy vault.
		$jwks = $this->get_sesamy_jwks();

		// Decode the JWKS.
		$jwks = json_decode( $jwks, true );

		// Strip Bearer from token.
		$jwt = str_replace( 'Bearer ', '', $jwt );

		// Parse JWKS to create a JWKSet object.
		$jwk_set = JOSE_JWK::decode( $jwks );

		// Create a JWS object from the JWT.
		$jws = JOSE_JWT::decode( $jwt );

		try {
			// Verify the signature.
			$verified = $jws->verify( $jwk_set );

			if ( $verified ) {
				return true;
			} else {
				return false;
			}
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * GET JWK Token by API.
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 */
	public function get_sesamy_jwks() {
		$req = wp_remote_get( Sesamy::$instance->get_assets_url() . '/vault-jwks.json' );
		return wp_remote_retrieve_body( $req );
	}
}
