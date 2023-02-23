<?php


function sesamy_content_container( $atts = null, $content = null ) {
	$post_settings = sesamy_get_post_settings( get_the_ID() );

	$atts = shortcode_atts(
		array(
			'publisher_content_id' => '',
			'preview'              => get_the_excerpt(),
			'item_src'             => get_permalink(),
			'show_childs_count'    => '',
			'gradient'             => '',
			'lock_mode'            => get_option( 'sesamy_lock_mode' ),
			'access_url'           => get_site_url() . '/wp-json/sesamy/v1/posts/' . $atts['publisher_content_id'],
			'pass'                 => '',
		),
		$atts,
		'sesamy_content_container'
	);

	// If lock mode is none, we should not wrap or do anything with the content
	if ( 'none' === $atts['lock_mode'] ) {
		return $content;
	}

	$tag_content = '<div slot="preview">' . $atts['preview'] . '</div>';

	if ( 'embed' === $atts['lock_mode'] ) {
		$tag_content .= '<div slot="content">' . $content . '</div>';
	}

	if ( 'signedUrl' !== $atts['lock_mode'] ) {
		unset( $atts['access_url'] );
	}

	// Exclude attributes used by WordPress when making sesamy tag
	$non_display_atts = array( 'preview' );
	$html_attributes  = array_filter(
		$atts,
		function ( $key ) use ( $non_display_atts ) {
			return ! in_array( $key, $non_display_atts, true );
		},
		ARRAY_FILTER_USE_KEY
	);

	return apply_filters( 'sesamy_content_container', Sesamy_Utils::make_tag( 'sesamy-content-container', $html_attributes, $tag_content ) );
}

function sesamy_button_container( $atts = null, $content = null ) {
	$atts = shortcode_atts(
		array(
			'publisher_content_id' => '',
			'item_src'             => '',
			'description '         => '',
		),
		$atts,
		'sesamy_button_container'
	);

	return Sesamy_Utils::make_tag( 'sesamy-button-container', $atts, $content, false );
}

function sesamy_button( $atts = null, $content = null ) {
	$atts = shortcode_atts(
		array(
			'text'                 => '',
			'price'                => '',
			'currency'             => '',
			'item_src'             => '',
			'publisher_content_id' => '',
		),
		$atts,
		'sesamy_button'
	);

	return Sesamy_Utils::make_tag( 'sesamy-button', $atts, $content, false );
}

function sesamy_login( $atts = null, $content = null ) {
	$atts = shortcode_atts(
		array(
			'client_id'   => '',
			'login_text'  => '',
			'logout_text' => '',
		),
		$atts,
		'sesamy_login'
	);

	return Sesamy_Utils::make_tag( 'sesamy-login', $atts, $content, false );
}

function sesamy_profile( $atts = null, $content = null ) {
	$atts = shortcode_atts(
		array(
		// Add here if needed
		),
		$atts,
		'sesamy_profile'
	);

	return Sesamy_Utils::make_tag( 'sesamy-profile', $atts, $content, false );
}

/**
 * Render the config tag
 */
function sesamy_config( $atts, $content ) {
	$atts = shortcode_atts(
		array(
			'client_id' => '',
			'currency'  => '',
		),
		$atts,
		'sesamy_config'
	);

	return Sesamy_Utils::make_tag( 'sesamy-config', $atts, $content, false );
}

function sesamy_get_enabled_post_types() {
	return get_option( 'sesamy_content_types', array() );

}


/**
 * Return information about a pass in sesamy in a easily accessible way
 */
function sesamy_get_pass_info( $term ) {
	$image_id = get_term_meta( $term->term_id, 'image_id', true );

	return array(
		'id'           => $term->slug,
		'title'        => $term->name,
		'description'  => $term->description,
		'price'        => get_term_meta( $term->term_id, 'price', true ),
		'currency'     => get_term_meta( $term->term_id, 'currency', true ),
		'image'        => ! empty( $image_id ) ? wp_get_attachment_image_url( $image_id, 'full' ) : null,
		'url'          => get_term_meta( $term->term_id, 'url', true ),
		'item_src'     => get_site_url() . '/wp-json/sesamy/v1/passes/' . $term->term_id,
		'period'       => get_term_meta( $term->term_id, 'period', true ),
		'time'         => get_term_meta( $term->term_id, 'time', true ),
		'product_type' => 'RECURRING',
	);

}

/**
 * Return information about sesamy settings for a post in an easy accessible way
 */
function sesamy_get_post_settings( $post_id ) {
	$post   = get_post( $post_id );
	$meta   = get_post_meta( $post_id );
	$passes = get_the_terms( $post->ID, 'sesamy_passes' );

	return array(
		'locked'                 => $meta['_sesamy_locked'],
		'enable_single_purchase' => $meta['_sesamy_enable_single_purchase'],
		'price'                  => $meta['_sesamy_price'],
		'currency'               => $meta['_sesamy_currency'],
		'passes'                 => is_array( $passes ) ? array_map( 'sesamy_get_pass_info', $passes ) : array(),
	);

}

/**
 * Return an array with the pass ids
 */
function sesamy_get_passes( $post_id_or_passes ) {
	if ( is_array( $post_id_or_passes ) ) {
		// Array of passes
		$passes = $post_id_or_passes;
	} elseif ( $post_id_or_passes instanceof WP_Post ) {
		// It's a WP_Post
		$passes = sesamy_get_post_settings( $post_id_or_passes->ID )['passes'];
	} elseif ( is_numeric( $post_id_or_passes ) ) {
		// A post ID
		$passes = sesamy_get_post_settings( $post_id_or_passes )['passes'];
	}

	$pass_names = is_array( $passes ) ? array_map(
		function ( $p ) {
			return ! empty( $p['id'] ) ? $p['id'] : null;
		},
		$passes
	) : array();

	return implode( ',', $pass_names );
}
