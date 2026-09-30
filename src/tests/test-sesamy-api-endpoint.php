<?php

use phpseclib\Crypt\RSA;

class Test_Sesamy_Api_Endpoint extends WP_UnitTestCase {

	const KID = 'test-kid';

	private static $private_key;
	private static $jwk;
	private static $other_private_key;

	private $jwks_requests = 0;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		$rsa  = new RSA();
		$keys = $rsa->createKey( 2048 );

		self::$private_key = new RSA();
		self::$private_key->loadKey( $keys['privatekey'] );

		$public_key = new RSA();
		$public_key->loadKey( $keys['publickey'] );
		self::$jwk = JOSE_JWK::encode( $public_key, array( 'kid' => self::KID, 'alg' => 'RS256', 'use' => 'sig' ) )->components;

		$other                   = $rsa->createKey( 2048 );
		self::$other_private_key = new RSA();
		self::$other_private_key->loadKey( $other['privatekey'] );
	}

	public function set_up() {
		parent::set_up();
		$this->jwks_requests = 0;
		$this->seed_jwks( array( self::$jwk ) );

		// Serve the test JWKS instead of calling auth2.sesamy.com.
		add_filter( 'pre_http_request', array( $this, 'mock_jwks_request' ), 10, 3 );
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', array( $this, 'mock_jwks_request' ), 10 );
		delete_transient( Sesamy_JWT_Helper::JWKS_TRANSIENT );
		parent::tear_down();
	}

	public function mock_jwks_request( $preempt, $args, $url ) {
		if ( Sesamy_JWT_Helper::get_jwks_url() !== $url ) {
			return $preempt;
		}
		++$this->jwks_requests;
		return array(
			'headers'  => array(),
			'body'     => wp_json_encode( array( 'keys' => array( self::$jwk ) ) ),
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
		);
	}

	private function seed_jwks( $keys, $fetched_at = null ) {
		set_transient(
			Sesamy_JWT_Helper::JWKS_TRANSIENT,
			array(
				'keys'       => $keys,
				'fetched_at' => null === $fetched_at ? time() : $fetched_at,
			),
			HOUR_IN_SECONDS
		);
	}

	private function token( $claims = array(), $header = array(), $key = null ) {
		$claims = array_merge(
			array(
				'permissions' => array( 'vault:entitlement:manage' ),
				'exp'         => time() + 300,
			),
			$claims
		);
		$jwt         = new JOSE_JWT( array_filter( $claims, fn( $v ) => null !== $v ) );
		$jwt->header = array_merge( $jwt->header, array( 'kid' => self::KID ), $header );
		return $jwt->sign( $key ?? self::$private_key, 'RS256' )->toString();
	}

	private function create_post( $locked, $args = array() ) {
		return wp_insert_post(
			array_merge(
				array(
					'post_title'   => 'Test post',
					'post_content' => 'Secret paid content',
					'post_status'  => 'publish',
					'post_author'  => 1,
					'meta_input'   => array(
						'_sesamy_locked' => $locked,
					),
				),
				$args
			)
		);
	}

	private function request( $post_id, $authorization = null ) {
		$request = new WP_REST_Request( 'GET', '/sesamy/v1/posts/' . $post_id );
		if ( null !== $authorization ) {
			$request->set_header( 'Authorization', $authorization );
		}
		return rest_get_server()->dispatch( $request );
	}

	private function assert_status( $status, $response ) {
		$this->assertSame( $status, $response->get_status() );
		if ( 200 !== $status ) {
			$this->assertStringNotContainsString( 'Secret paid content', wp_json_encode( $response->get_data() ) );
		}
	}

	public function test_locked_post_without_authorization_is_rejected() {
		$this->assert_status( 401, $this->request( $this->create_post( true ) ) );
	}

	public function test_locked_post_with_non_bearer_authorization_is_rejected() {
		$this->assert_status( 401, $this->request( $this->create_post( true ), 'Basic dXNlcjpwYXNz' ) );
	}

	public function test_locked_post_with_empty_bearer_is_rejected() {
		$this->assert_status( 401, $this->request( $this->create_post( true ), 'Bearer ' ) );
	}

	public function test_locked_post_with_malformed_token_is_rejected() {
		$this->assert_status( 401, $this->request( $this->create_post( true ), 'Bearer not-a-jwt' ) );
	}

	public function test_locked_post_with_token_signed_by_unknown_key_is_rejected() {
		$token = $this->token( array(), array(), self::$other_private_key );
		$this->assert_status( 401, $this->request( $this->create_post( true ), 'Bearer ' . $token ) );
	}

	public function test_locked_post_with_alg_none_is_rejected() {
		$jwt         = new JOSE_JWT( array( 'permissions' => array( 'vault:entitlement:manage' ) ) );
		$jwt->header = array(
			'typ' => 'JWT',
			'alg' => 'none',
			'kid' => self::KID,
		);
		$this->assert_status( 401, $this->request( $this->create_post( true ), 'Bearer ' . $jwt->toString() ) );
	}

	public function test_locked_post_with_hs256_token_is_rejected() {
		$jwt         = new JOSE_JWT( array( 'permissions' => array( 'vault:entitlement:manage' ) ) );
		$jwt->header = array_merge( $jwt->header, array( 'kid' => self::KID ) );
		$token       = $jwt->sign( wp_json_encode( self::$jwk ), 'HS256' )->toString();
		$this->assert_status( 401, $this->request( $this->create_post( true ), 'Bearer ' . $token ) );
	}

	public function test_locked_post_with_expired_token_is_rejected() {
		$token = $this->token( array( 'exp' => time() - 10 ) );
		$this->assert_status( 401, $this->request( $this->create_post( true ), 'Bearer ' . $token ) );
	}

	public function test_locked_post_without_permission_is_forbidden() {
		$token = $this->token( array( 'permissions' => array( 'something:else' ) ) );
		$this->assert_status( 403, $this->request( $this->create_post( true ), 'Bearer ' . $token ) );
	}

	public function test_locked_post_without_permissions_claim_is_forbidden() {
		$token = $this->token( array( 'permissions' => null ) );
		$this->assert_status( 403, $this->request( $this->create_post( true ), 'Bearer ' . $token ) );
	}

	public function test_locked_post_with_valid_token_returns_content() {
		$response = $this->request( $this->create_post( true ), 'Bearer ' . $this->token() );

		$this->assert_status( 200, $response );
		$this->assertStringContainsString( 'Secret paid content', $response->get_data()['data'] );
		$this->assertSame( 0, $this->jwks_requests );
	}

	public function test_jwks_is_fetched_when_not_cached() {
		delete_transient( Sesamy_JWT_Helper::JWKS_TRANSIENT );

		$this->assert_status( 200, $this->request( $this->create_post( true ), 'Bearer ' . $this->token() ) );
		$this->assert_status( 200, $this->request( $this->create_post( true ), 'Bearer ' . $this->token() ) );
		$this->assertSame( 1, $this->jwks_requests );
	}

	public function test_unknown_kid_refetches_stale_jwks_once() {
		// A cached JWKS from before the key rotation.
		$this->seed_jwks( array(), time() - HOUR_IN_SECONDS / 2 );

		$this->assert_status( 200, $this->request( $this->create_post( true ), 'Bearer ' . $this->token() ) );
		$this->assertSame( 1, $this->jwks_requests );
	}

	public function test_unknown_kid_does_not_refetch_fresh_jwks() {
		$this->seed_jwks( array() );

		$this->assert_status( 401, $this->request( $this->create_post( true ), 'Bearer ' . $this->token() ) );
		$this->assertSame( 0, $this->jwks_requests );
	}

	public function test_unlocked_post_without_authorization_returns_content() {
		$response = $this->request( $this->create_post( false ) );

		$this->assert_status( 200, $response );
		$this->assertStringContainsString( 'Secret paid content', $response->get_data()['data'] );
	}

	public function test_unpublished_posts_are_not_found() {
		foreach ( array( 'draft', 'private', 'pending', 'future' ) as $status ) {
			$args = array( 'post_status' => $status );
			if ( 'future' === $status ) {
				$args['post_date'] = gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS );
			}
			$this->assert_status( 404, $this->request( $this->create_post( false, $args ) ) );
			$this->assert_status( 404, $this->request( $this->create_post( true, $args ), 'Bearer ' . $this->token() ) );
		}
	}

	public function test_password_protected_posts_are_not_found() {
		$args = array( 'post_password' => 'secret' );
		$this->assert_status( 404, $this->request( $this->create_post( false, $args ) ) );
		$this->assert_status( 404, $this->request( $this->create_post( true, $args ), 'Bearer ' . $this->token() ) );
	}
}
