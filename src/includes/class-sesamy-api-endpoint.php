<?php



class Sesamy_Content_Endpoint {



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
					'sp' => array(
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
			'/passes/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'sesamy_passes_details_ep' ),
				'permission_callback' => '__return_true',
			)
		);

	}

	/**
	 * Validation callback for arguments
	 */
	function validate_numeric_param( $param, $request, $key ) {
		return is_numeric( $param );
	}

	/**
	 * Endpoint for validating request and returning the content
	 */
	public function sesamy_post_ep( $request ) {

		$signed_url = new Sesamy_Signed_Url();

		$public_signed_url = esc_url_raw( $_SERVER['HTTP_X_SESAMY_SIGNED_URL'] );

		$result = $signed_url->is_valid_link( $public_signed_url );

		if ( is_wp_error( $result ) ) {
			return $result;
		} elseif ( is_bool( $result ) && true == $result ) {
			$params = $signed_url->get_request_parameters( $public_signed_url );

			if ( intval( $request['id'] ) != intval( $params['sp'] ) ) {
				return new WP_Error( 400, 'The supplied route id does not match the sp in supplied token' );
			}

			$post = get_post( $params['sp'] );

			return new WP_REST_Response( array( 'data' => apply_filters( 'the_content', $post->post_content ) ) );
		} else {

			return new WP_Error( 400, 'The link is incorrect or no longer valid.' );
		}

	}


	public function sesamy_passes_ep( $request ) {

		$passes = get_terms( 'sesamy_passes', array( 'hide_empty' => false ) );
		$data   = array_map( 'sesamy_get_pass_info', $passes );
		return rest_ensure_response( array_values( $data ) );

	}

	public function sesamy_passes_details_ep( $request ) {

		$term_id = $request['id'];
		$term    = get_term( $term_id, 'sesamy_passes' );
		return rest_ensure_response( sesamy_get_pass_info( $term ) );

	}

	/**
	 * Return content with applied filters
	 */
	function get_response( $post ) {

		return new WP_REST_Response( array( 'data' => apply_filters( 'the_content', $post->post_content ) ) );

	}


	/**
	 * Format response based on Accept header
	 */
	public function format_response( $served, $result, $request, $server ) {

		if ( preg_match( '/^\/sesamy\/v1\/posts\/[0-9]+$/m', $request->get_route() ) && isset( $result->data['data'] ) ) {

			switch ( $_SERVER['HTTP_ACCEPT'] ) {

				case 'text/html':
					header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
					echo $result->data['data'];
					exit;

				case 'application/xml':
					header( 'Content-Type: application/xml; charset=' . get_option( 'blog_charset' ) );

					$xmlDoc   = new DOMDocument();
					$response = $xmlDoc->appendChild( $xmlDoc->createElement( 'Data', $result->data['data'] ) );

					echo $xmlDoc->saveXML();
					exit;

			}
		}

	}


}
