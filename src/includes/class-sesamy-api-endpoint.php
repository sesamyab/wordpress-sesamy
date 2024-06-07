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
			return new WP_Error( 404, __( 'Post not found.', 'sesamy' ) );
		}

		// Get JWT token from the authorization header.
		$jwt = isset( $_SERVER['HTTP_AUTHORIZATION'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) ) : '';

		// If the post is locked, verify the JWT token. If not, just return the content.
		$sesamy_helper_obj = new Sesamy_JWT_Helper();
		$result            = Sesamy::is_locked( $post ) && preg_match( '/^\s*Bearer/i', $jwt ) ? $sesamy_helper_obj->verify( $jwt ) : true;

		if ( is_wp_error( $result ) ) {
			return $result;
		} elseif ( is_bool( $result ) && true === $result ) {
			return new WP_REST_Response( array( 'data' => apply_filters( 'the_content', $post->post_content ) ) );
		} else {
			return new WP_Error( 400, __( 'The link is incorrect or no longer valid.', 'sesamy' ) );
		}
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
