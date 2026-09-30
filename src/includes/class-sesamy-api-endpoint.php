<?php
/**
 * API Endpoint
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * This class Register API Endpoints.
 *
 * @since      1.0.0
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Api_Endpoint {
	/**
	 * Register API Endpoints.
	 *
	 * @since      1.0.0
	 * @package    Sesamy
	 */
	public function register_route() {

		register_rest_route(
			'sesamy/v1',
			'/posts/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'sesamy_post_ep' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'se' => array(
						'validate_callback' => array( $this, 'validate_numeric_param' ),
					),
					'ss' => array(),
				),
			)
		);

		register_rest_route(
			'sesamy/v1',
			'/passes',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'sesamy_passes_ep' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'sesamy/v1',
			'/passes/(?P<slug>[a-zA-Z0-9-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'sesamy_passes_details_ep' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'slug' => array(
						'type' => 'string',
					),
				),
			)
		);
	}

	/**
	 * Validation callback for arguments.
	 *
	 * @since      1.0.0
	 * @package    Sesamy
	 * @param string $param Parameters.
	 * @param string $request Request method.
	 * @param string $key Validate Key.
	 */
	public function validate_numeric_param( $param, $request, $key ) {
		return is_numeric( $param );
	}

	/**
	 * Endpoint for validating request and returning the content
	 *
	 * @since      1.0.0
	 * @package    Sesamy
	 * @param string $request Request method.
	 */
	public function sesamy_post_ep( $request ) {

		$post = get_post( $request['id'] );

		// Check that post actually exists.
		if ( null === $post ) {
			return new WP_Error( 'sesamy_post_not_found', __( 'Post not found.', 'sesamy' ), array( 'status' => 404 ) );
		}

		// Only published posts without a password are served.
		if ( 'publish' !== $post->post_status || '' !== $post->post_password ) {
			return new WP_Error( 'sesamy_post_not_found', __( 'Post not found.', 'sesamy' ), array( 'status' => 404 ) );
		}

		// If the post is locked, a valid access token is required. If not, just return the content.
		if ( Sesamy::is_locked( $post ) ) {
			$result = $this->authorize( $request );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		return new WP_REST_Response( array( 'data' => apply_filters( 'the_content', $post->post_content ) ) );
	}

	/**
	 * Verify the Bearer token in the authorization header. Fails closed when the header is missing or invalid.
	 *
	 * @since      3.0.12
	 * @package    Sesamy
	 * @param WP_REST_Request $request Request.
	 * @return true|WP_Error
	 */
	public function authorize( $request ) {

		$unauthorized = new WP_Error( 'sesamy_unauthorized', __( 'A valid access token is required.', 'sesamy' ), array( 'status' => 401 ) );

		// Get JWT token from the authorization header.
		$auth_header = (string) $request->get_header( 'authorization' );
		if ( ! preg_match( '/^\s*Bearer\s+(\S+)\s*$/i', $auth_header, $matches ) ) {
			return $unauthorized;
		}

		try {
			$claims = $this->get_jwt_helper()->decode( $matches[1] );
		} catch ( Throwable $e ) {
			return $unauthorized;
		}

		if ( is_wp_error( $claims ) ) {
			return new WP_Error( 'sesamy_jwks_unavailable', __( 'Could not verify the access token.', 'sesamy' ), array( 'status' => 503 ) );
		}
		if ( ! is_array( $claims ) ) {
			return $unauthorized;
		}

		if ( ! isset( $claims['permissions'] ) || ! is_array( $claims['permissions'] ) || ! in_array( 'vault:entitlement:manage', $claims['permissions'], true ) ) {
			return new WP_Error( 'sesamy_forbidden', __( 'The access token does not grant access to this content.', 'sesamy' ), array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Get the JWT helper used to verify tokens.
	 *
	 * @since      3.0.12
	 * @package    Sesamy
	 * @return Sesamy_JWT_Helper
	 */
	protected function get_jwt_helper() {
		return new Sesamy_JWT_Helper();
	}

	/**
	 * API enpoint callback function for passes.
	 *
	 * @since      1.0.0
	 * @package    Sesamy
	 * @param string $request Request method.
	 */
	public function sesamy_passes_ep( $request ) {

		$passes = get_terms(
			array(
				'taxonomy'   => 'sesamy_passes',
				'hide_empty' => false,
			),
		);
		$data   = array_map( 'sesamy_get_pass_info', $passes );
		return new WP_REST_Response( array_values( $data ) );
	}

	/**
	 * API enpoint callback function for passes details.
	 *
	 * @since      1.0.0
	 * @package    Sesamy
	 * @param string $request Request method.
	 */
	public function sesamy_passes_details_ep( $request ) {

		$term_slug = sanitize_text_field( $request['slug'] );
		$term      = get_term_by( 'slug', $term_slug, 'sesamy_passes' );

		if ( false == $term ) {
			return new WP_Error( 'sesamy_pass_not_found', __( 'Pass not found', 'sesamy' ), array( 'status' => 404 ) );
		} else {
			return new WP_REST_Response( sesamy_get_pass_info( $term ) );
		}
	}


	/**
	 * Format response based on Accept header.
	 *
	 * @since      1.0.0
	 * @package    Sesamy
	 * @param string $served Served.
	 * @param array  $result Results format.
	 * @param string $request Request method.
	 * @param string $server server.
	 */
	public function format_response( $served, $result, $request, $server ) {

		if ( 1 === preg_match( '/^\/sesamy\/v1\/.*$/m', $request->get_route() ) && isset( $result->data['data'] ) ) {

			if ( isset( $_SERVER['HTTP_ACCEPT'] ) ) {

				switch ( $_SERVER['HTTP_ACCEPT'] ) {

					case 'text/html':
						header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
						if ( ! empty( $result ) ) {
							echo wp_kses_post( $result->data['data'], wp_allowed_protocols() );
						}
						exit;
				}
			}
		}
	}
}
