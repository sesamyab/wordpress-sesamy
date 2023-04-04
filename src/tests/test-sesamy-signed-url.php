<?php
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;
use phpseclib3\Crypt\RSA;

class Test_Sesamy_Signed_Url extends WP_UnitTestCase {

	private static $rsa;

	public static function setUpBeforeClass(): void {
		// Use same key pair for all tests
		self::$rsa = self::get_rsa();
	}

	/**
	 * Get RSA from test jwk file
	 *
	 * @return RSA
	 */
	private static function get_rsa(): RSA {

		$jwk = file_get_contents( __DIR__ . '/test_jwk.json' );
		return PublicKeyLoader::load( $jwk );
	}

	/**
	 * Sign data
	 *
	 * @param [type] $url
	 * @return string
	 */
	private static function sign( $data ): string {
		$signature = self::$rsa->sign( $data );
		return base64_encode( $signature );
	}

	/**
	 * Sign the url with test key
	 *
	 * @param [type] $url
	 * @return str
	 */
	private static function sign_url( $url ): string {

		$url .= ( strpos( $url, '?' ) === false ? '?' : '&' ) . 'se=' . ( time() + 3600 );
		$url .= '&ss=' . self::sign( $url );

		return $url;
	}

	/**
	 * Test that we verify links correctly
	 *
	 * @return void
	 */
	public function test_has_valid_url_signature() {

		// Arrange
		$signed_url    = self::sign_url( 'https://localhost/test-article' );
		$jwk           = file_get_contents( __DIR__ . '/test_jwk.json' );
		$signedUrlMock = \Mockery::mock( Sesamy_Signed_Url::class )->makePartial();
		$signedUrlMock->shouldReceive( 'get_sesamy_jwks' )->andReturn( $jwk );

		// Act
		$result = $signedUrlMock->has_valid_url_signature( $signed_url );

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * Test validation of url to a post with an assigned pass link
	 *
	 * @return void
	 */
	public function test_is_valid_url_with_pass_url() {

		// Arrange

		$created_term = wp_create_term( 'Pass One', 'sesamy_passes' );
		if ( is_wp_error( $created_term ) ) {
			$this->fail( $created_term );
		}
		$term_id = intval( $created_term['term_id'] );
		$term    = get_term( $term_id, 'sesamy_passes' );

		update_term_meta( $term_id, 'price', 99 );
		update_term_meta( $term_id, 'currency', 'SEK' );
		update_term_meta( $term_id, 'url', '' );
		update_term_meta( $term_id, 'period', 'monthly' );
		update_term_meta( $term_id, 'time', 1 );

		$post_id = wp_insert_post(
			array(
				'post_title'   => wp_strip_all_tags( 'Test post' ),
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => 1,
			)
		);

		$pass_info = sesamy_get_pass_info( $term_id );

		// Add term to post
		wp_set_object_terms( $post_id, $term_id, 'sesamy_passes', true );

		$signed_url    = self::sign_url( $pass_info['item_src'] );
		$jwk           = file_get_contents( __DIR__ . '/test_jwk.json' );
		$signedUrlMock = \Mockery::mock( Sesamy_Signed_Url::class )->makePartial();
		$signedUrlMock->shouldReceive( 'get_sesamy_jwks' )->andReturn( $jwk );

		// Act
		$result = $signedUrlMock->is_valid_url( $post_id, $signed_url );

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * Test validation of url to a post with the posts permalink
	 *
	 * @return void
	 */
	public function test_is_valid_url_with_post_url() {

		// Arrange
		$post_id = wp_insert_post(
			array(
				'post_title'   => wp_strip_all_tags( 'Test post' ),
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => 1,
			)
		);

		$signed_url    = self::sign_url( get_permalink( $post_id ) );
		$jwk           = file_get_contents( __DIR__ . '/test_jwk.json' );
		$signedUrlMock = \Mockery::mock( Sesamy_Signed_Url::class )->makePartial();
		$signedUrlMock->shouldReceive( 'get_sesamy_jwks' )->andReturn( $jwk );

		// Act
		$result = $signedUrlMock->is_valid_url( $post_id, $signed_url );

		// Assert
		$this->assertTrue( $result );
	}

}
