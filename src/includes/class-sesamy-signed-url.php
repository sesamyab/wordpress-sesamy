<?php
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class Sesamy_Signed_Url {

	/**
	 * True if the url is signed and valid for the post
	 *
	 * @param WP_POST | int $post
	 * @param str $url
	 * @return boolean
	 */
	public function is_valid_url( $post, $url ) {

		$post          = get_post( $post );
		$post_settings = sesamy_get_post_settings( $post );
		$permalink     = get_permalink( $post );

		// Do a quick regex to see if it is a post url to avoid expensive loop if not
		if ( substr( $url, 0, strlen( $permalink ) ) === $permalink  ) {
			return $this->has_valid_url_signature( $url );
		} else {

			// Test if the url is for a pass assigned to this post
			foreach ( $post_settings['passes'] as $pass ) {

				// Check if url starts with pass API url (in PHP < 8 comaptible way)
				if ( $pass['item_src'] === substr( $url, 0, strlen( $pass['item_src'] ) ) ) {
					return $this->has_valid_url_signature( $url );
				}
			}
		}

		return false;
	}


	/**
	 * Main function to test if a signed link is valid
	 *
	 * @param [type] $url
	 * @return boolean
	 */
	public function has_valid_url_signature( $url ): bool {

		$params = $this::get_request_parameters( $url );

		$expected_keys = array( 'se', 'ss' );

		if ( count( array_intersect( array_keys( $params ), $expected_keys ) ) !== count( $expected_keys ) ) {
			return false;
		}

		// Verify expiration
		if ( intval( $params['se'] ) < time() ) {
			return false;
		}

		// Split instead of looking at parsed url since we do not have the ss urlencoded properly

		$url = explode( 'ss=', $url );
		$ss  = $url[1];

		// Verify signature
		return $this::verify_signature( $params['signed_url'], base64_decode( $ss ) );
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
	public function verify_signature( $data, $signature ) {
		return $this::get_rsa()->verify( $data, $signature );
	}


	public function get_sesamy_jwks() {
		$req = wp_remote_get( Sesamy::$instance->get_assets_url() . '/vault-jwks.json' );
		$jwk = wp_remote_retrieve_body( $req );
	}

	/**
	 * Get the public RSA object
	 */
	public function get_rsa() {

		$jwk = get_transient( 'sesamy_public_jwk' );

		// Use transient to avoid calling api more than needed
		if ( false === $jwk ) {

			$jwk = $this->get_sesamy_jwks();
			set_transient( 'sesamy_public_jwk', $jwk, 3600 );

		}

		return PublicKeyLoader::load( $jwk )->getPublicKey();
	}
}
