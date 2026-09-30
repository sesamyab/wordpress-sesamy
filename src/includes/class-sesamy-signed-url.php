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

/**
 * Verifies Sesamy access tokens against the Sesamy auth JWKS.
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_JWT_Helper {

	/**
	 * Transient used to cache the JWKS.
	 */
	const JWKS_TRANSIENT = 'sesamy_auth_jwks';

	/**
	 * How long the JWKS is cached. Keys rotate, so keep this short.
	 */
	const JWKS_TTL = HOUR_IN_SECONDS;

	/**
	 * Minimum age of the cached JWKS before an unknown kid triggers a refetch.
	 */
	const JWKS_REFETCH_INTERVAL = 5 * MINUTE_IN_SECONDS;

	/**
	 * The only accepted signing algorithm.
	 */
	const ALGORITHM = 'RS256';

	/**
	 * True if the token has a valid signature from Sesamy.
	 *
	 * @param string $jwt JWT Token, optionally prefixed with "Bearer ".
	 * @since  1.0.0
	 * @package    Sesamy
	 * @return boolean
	 */
	public function verify( $jwt ) {
		return is_array( $this->decode( $jwt ) );
	}

	/**
	 * Verify the token and return its claims.
	 *
	 * Fails closed: returns false for anything that isn't an unexpired RS256 token signed by a key in the Sesamy JWKS.
	 *
	 * @param string $jwt JWT Token, optionally prefixed with "Bearer ".
	 * @since  3.0.12
	 * @package    Sesamy
	 * @return array|false|WP_Error Claims, false if the token is invalid, or WP_Error if the JWKS can't be fetched.
	 */
	public function decode( $jwt ) {

		// Strip Bearer from token.
		$jwt = trim( preg_replace( '/^\s*Bearer\s+/i', '', (string) $jwt ) );

		try {
			$token = JOSE_JWT::decode( $jwt );
		} catch ( Throwable $e ) {
			return false;
		}

		// Only signed tokens with a pinned algorithm and a key id are accepted.
		if ( ! $token instanceof JOSE_JWT || $token instanceof JOSE_JWE ) {
			return false;
		}
		if ( ! isset( $token->header['alg'], $token->header['kid'] ) || self::ALGORITHM !== $token->header['alg'] || ! is_string( $token->header['kid'] ) ) {
			return false;
		}

		$key = $this->find_key( $token->header['kid'] );
		if ( is_wp_error( $key ) ) {
			return $key;
		}
		if ( null === $key ) {
			return false;
		}

		try {
			$token->verify( JOSE_JWK::decode( $key ), self::ALGORITHM );
		} catch ( Throwable $e ) {
			return false;
		}

		$claims = $token->claims;
		$now    = time();
		if ( isset( $claims['exp'] ) && ( ! is_numeric( $claims['exp'] ) || $now >= (int) $claims['exp'] ) ) {
			return false;
		}
		if ( isset( $claims['nbf'] ) && ( ! is_numeric( $claims['nbf'] ) || $now < (int) $claims['nbf'] ) ) {
			return false;
		}

		return $claims;
	}

	/**
	 * Find the JWK for a key id, refetching the JWKS once if the kid is unknown.
	 *
	 * @param string $kid Key id.
	 * @since  3.0.12
	 * @package    Sesamy
	 * @return array|null|WP_Error JWK components, null if not found, or WP_Error if the JWKS can't be fetched.
	 */
	private function find_key( $kid ) {
		$jwks = $this->get_sesamy_jwks();
		if ( is_wp_error( $jwks ) ) {
			return $jwks;
		}

		$key = $this->key_by_kid( $jwks, $kid );

		// Keys rotate. Refetch on an unknown kid, but not more often than the refetch interval.
		if ( null === $key && time() - $jwks['fetched_at'] >= self::JWKS_REFETCH_INTERVAL ) {
			$jwks = $this->get_sesamy_jwks( true );
			if ( is_wp_error( $jwks ) ) {
				return $jwks;
			}
			$key = $this->key_by_kid( $jwks, $kid );
		}

		return $key;
	}

	/**
	 * Find an RSA key by key id in a JWKS.
	 *
	 * @param array  $jwks JWKS.
	 * @param string $kid Key id.
	 * @return array|null
	 */
	private function key_by_kid( $jwks, $kid ) {
		foreach ( $jwks['keys'] as $key ) {
			if ( is_array( $key ) && isset( $key['kid'], $key['kty'] ) && $kid === $key['kid'] && 'RSA' === $key['kty'] ) {
				return $key;
			}
		}
		return null;
	}

	/**
	 * Get the Sesamy auth JWKS, cached in a transient.
	 *
	 * @param boolean $force_refresh Skip the cache.
	 * @since  1.0.0
	 * @package    Sesamy
	 * @return array|WP_Error Array with 'keys' and 'fetched_at', or WP_Error on failure.
	 */
	public function get_sesamy_jwks( $force_refresh = false ) {
		if ( ! $force_refresh ) {
			$cached = get_transient( self::JWKS_TRANSIENT );
			if ( is_array( $cached ) && isset( $cached['keys'], $cached['fetched_at'] ) ) {
				return $cached;
			}
		}

		$req = wp_remote_get( self::get_jwks_url() );
		if ( is_wp_error( $req ) ) {
			return $req;
		}

		$code = (int) wp_remote_retrieve_response_code( $req );
		if ( 200 !== $code ) {
			return new WP_Error( 'sesamy_jwks_http_error', sprintf( 'JWKS fetch failed (HTTP %d).', $code ) );
		}

		$decoded = json_decode( (string) wp_remote_retrieve_body( $req ), true );
		if ( ! is_array( $decoded ) || ! isset( $decoded['keys'] ) || ! is_array( $decoded['keys'] ) ) {
			return new WP_Error( 'sesamy_jwks_invalid_json', 'JWKS response was not valid.' );
		}

		$jwks = array(
			'keys'       => $decoded['keys'],
			'fetched_at' => time(),
		);
		set_transient( self::JWKS_TRANSIENT, $jwks, self::JWKS_TTL );

		return $jwks;
	}

	/**
	 * URL of the Sesamy auth JWKS.
	 *
	 * @since  3.0.12
	 * @return string
	 */
	public static function get_jwks_url() {
		$tld = ( defined( 'SESAMY_DEV_API' ) && true === SESAMY_DEV_API ) ? 'dev' : 'com';
		return "https://auth2.sesamy.{$tld}/.well-known/jwks.json";
	}
}
