<?php

use WP_Mock\Tools\TestCase;
use Jose\Component\Core\JWK;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\RS256;


class Sesamy_Signed_Url_Test extends TestCase  {

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Sign the url with dummy key
	 *
	 * @param [type] $url
	 * @return str
	 */
	private static function get_dummy_signed_url( $url ): string {

		$url .= '?se=' . time();
		$url .= '&ss=' . self::get_dummy_signature( $url );

		return $url;
	}

	private static function get_dummy_signature( $url ): string {
		return 'ABC';
		$algorithm_manager = new AlgorithmManager(
			array(
				new RS256(),
			)
		);

		$rs256 = $algorithm_manager->get( 'RS256' );
		$jwk   = self::get_dummy_jwks();

		return $rs256->sign( $jwk, $url );
	}

	private static function get_dummy_jwks(): JWK {
		$json = file_get_contents( __DIR__ . '/dummy_jwks.json' );
		$jwk  = JWK::createFromJson( $json );
		return $jwk;
	}

	public function test() {
		$this->assertSame( true, true );
	}

	public function test_is_valid_link_pass() {

		// Arrange
		$signed_url = self::get_dummy_signed_url( 'https://localhost/test-article' );
		$post_id    = 1;

		// Act
		$result = Sesamy_Signed_Url::is_valid_link( $post_id, $signed_url );

		// Assert
		$self->assertTrue( $result );
	}

}
