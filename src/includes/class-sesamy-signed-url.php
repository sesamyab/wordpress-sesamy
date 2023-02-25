<?php
use Jose\Component\Core\JWK;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\RS256;


class Sesamy_Signed_Url {


	/**
	 * Main function to test if a signed link is valid
	 */
	public function is_valid_link( $url ) {

		$params = $this->get_request_parameters( $url );

		$expected_keys = array( 'sp', 'se', 'ss' );

		if ( count( array_intersect( array_keys( $params ), $expected_keys ) ) !== count( $expected_keys ) ) {
			return new WP_Error( 404, 'Missing request parameters' );
		}

		$post = get_post( $params['sp'] );

		if ( null === $post ) {
			return new WP_Error( 404, 'Item not found' );
		}

		// Check if post is locked, if not, just return content
		$is_locked = get_post_meta( $post->ID, '_sesamy_locked', true );

		if ( ! $is_locked ) {
			return true;
		}

		// Verify expiration
		if ( $params['se'] < time() ) {
			return new WP_Error( 400, 'The link is expired' );
		}

		// Fix for not having an urlencoded ss

		$url = explode( 'ss=', $url );
		$ss  = $url[1];

		// Verify signature
		if ( $this->verify_signature( $params['signed_url'], base64_decode( $ss ) ) ) {
			return true;
		} else {
			return new WP_Error( 400, 'The signature is invalid.' );
		}
	}

	/**
	 * Get parameters for the request.
	 */
	public function get_request_parameters( $url ) {

		$query_string = wp_parse_url( $url, PHP_URL_QUERY );
		parse_str( $query_string, $parts );
		array_flip( $parts );

		// Get the url part without signature part
		$split_url = explode( '&ss=', $url );

		return array_merge( array( 'signed_url' => $split_url[0] ), $parts );
	}

	/**
	 * Validate signature with
	 */
	public function verify_signature( $url, $signature ) {

		$algorithm_manager = new AlgorithmManager(
			array(
				new RS256(),
			)
		);

		$rs256 = $algorithm_manager->get( 'RS256' );
		$jwk   = $this->get_public_key();

		return $rs256->verify( $jwk, $url, $signature );
	}



	/**
	 * Get the public key
	 */
	public function get_public_key() {

		$jwk = get_transient( 'sesamy_public_key' );

		// Use transient to avoid calling api more than needed
		if ( false === $jwk ) {

			$req  = wp_remote_get( Sesamy::$instance->get_assets_url() . '/vault-jwks.json' );
			$json = wp_remote_retrieve_body( $req );

			$jwk = JWK::createFromJson( $json );
			set_transient( 'sesamy_public_key', $jwk, 3600 );

		}

		return $jwk;
	}

}
