<?php
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;

class Sesamy_JWT_Helper {

	/**
	 * True if the url is signed and valid for the post
	 *
	 * @param str $jwtToken
	 * @return boolean
	 */
	public function verify( $jwt ) {

		// Get the public key from sesamy vault
		$jwks = $this->get_sesamy_jwks();

		// Decode the JWKS
		$jwks = json_decode($jwks, true);

		// Strip Bearer from token
		$jwt = str_replace('Bearer ', '', $jwt);

		// Parse JWKS to create a JWKSet object
		$JWKSet = JOSE_JWK::decode($jwks);
		
		// Create a JWS object from the JWT
		$JWS = JOSE_JWT::decode($jwt);

		try {
			// Verify the signature
			$verified = $JWS->verify($JWKSet);

			if ($verified)
				return true;
			else
				return false;

		} catch (Exception $e) {
			return false;
		}
	}

	public function get_sesamy_jwks() {
		$req = wp_remote_get( Sesamy::$instance->get_assets_url() . '/vault-jwks.json' );
		return wp_remote_retrieve_body( $req );
	}
}
