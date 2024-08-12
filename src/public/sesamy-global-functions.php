<?php
/**
 * Plugin global functions
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * Get Content container
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content Content.
 */
function get_sesamy_content_container( $atts = null, $content = null ) {
	ob_start();
	sesamy_content_container( $atts, $content );
	return ob_get_clean();
}

/**
 * Content container
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content Content.
 */
function sesamy_content_container( $atts = null, $content = null ) {
	$post_id = get_the_ID();
	$post_settings = sesamy_get_post_settings( $post_id );
	if ( $post_settings['access_level'] != -1 && !empty( $post_settings['access_level'] ) ) {
		$access_level = $post_settings['access_level'];	
	} else {
		$access_level = 'entitlement';
	}
	
	// Get sesamy tags and add in container argument
	if ( !empty( $post_settings['sesamy_tags'] ) ) {
		$required_tags = explode("|", $post_settings['sesamy_tags']);
	} else {
		$required_tags = get_option('sesamy_tags');
	}

	$tag_name = [];
	if(isset($required_tags) && is_array($required_tags)) {
		foreach($required_tags as $tag) {
			$tag_array = get_term_by("term_id", $tag, "sesamy_tags");
			if($tag_array) {
				$get_term_meta = get_term_meta($tag_array->term_id, "attribute_type", true);
				array_push($tag_name, ($get_term_meta) ? $get_term_meta.":".$tag_array->slug : $tag_array->slug);
			}
		}
		$required_tags = $tag_name;
	}
	$required_tags = ($required_tags) ? implode(";", $required_tags) : "";

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
			'access-level' 		   => $access_level,
			'required-attributes'  => $required_tags,
		),
		$atts,
		'sesamy_content_container'
	);

	// If the article is in lock mode None, it's not locked or it has access level public, treat it as embed and public.
	$is_public = $atts['lock_mode'] === 'none' || !Sesamy_Post_Properties::is_locked( $post_id ) || $access_level === 'public';
	if ( $is_public ) {
		$atts['lock_mode'] = 'embed';
		$atts['public'] = 'true';
	}

	ob_start();

	// Exclude attributes used by WordPress when making sesamy tag.
	$non_display_atts = array( 'preview' );

	// Only the 'entitlement' access level is supported to use with the 'signedUrl' and 'event' lock modes.
	// If the content is not 'entitlement', we change the lock mode to 'embed', so that the content unlocks correctly.
	if ( ( 'signedUrl' === $atts['lock_mode'] || 'event' === $atts['lock_mode'] ) && $access_level != 'entitlement' ) {
		$atts['lock_mode'] = 'embed';
	}

	$html_attributes  = array_filter(
		$atts,
		function ( $key ) use ( $non_display_atts ) {
			return ! in_array( $key, $non_display_atts, true );
		},
		ARRAY_FILTER_USE_KEY
	);

	echo '<sesamy-content-container ';
	Sesamy_Utils::html_attributes( $html_attributes );
	echo '/>';

	if ( ! isset( $atts['public'] ) ) {
		echo '<div slot="preview">' . $atts['preview'] . '</div>';
	}

	if ( 'embed' === $atts['lock_mode'] ) {
		echo '<div slot="content">' . $content . '</div>';
	}

	if ( 'encode' === $atts['lock_mode'] ) {
		echo '<div slot="content">' . base64_encode( $content ) . '</div>';
	}

	if ( 'signedUrl' !== $atts['lock_mode'] ) {
		unset( $atts['access_url'] );
	}

	echo '</sesamy-content-container>';

	// These hoops are here to get WordPress checks for not echoing unescaped content happy.
	// The "_clean" suffix is reserved for marking a variable as clean according to the developer handbook.

	$container_content       = ob_get_clean();
	$content_container_clean = apply_filters( 'sesamy_content_container', $container_content );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $content_container_clean;
}

/**
 * GEt Button container
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content Button Content.
 */
function get_sesamy_button_container( $atts = null, $content = null ) {
	ob_start();
	sesamy_button_container( $atts, $content );
	return ob_get_clean();
}

/**
 * Button container
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content Button Content.
 */
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

	Sesamy_Utils::make_tag( 'sesamy-button-container', $atts, $content, false );
}

/**
 * GEt Button HTML
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content Button Content.
 */
function get_sesamy_button( $atts = null, $content = null ) {
	ob_start();
	sesamy_button( $atts, $content );
	return ob_get_clean();
}

/**
 * Button Shortcode With attributes
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content Button Content.
 */
function sesamy_button( $atts = null, $content = null ) {
	$atts = shortcode_atts(
		array(
			'text'                 => '',
			'price'                => '',
			'currency'             => '',
			'item_src'             => '',
			'publisher_content_id' => '',
			'hide_price'           => '',
			'hide_logo'            => '',
			'checkout_version'     => '2',
		),
		$atts,
		'sesamy_button'
	);
	Sesamy_Utils::make_tag( 'sesamy-button', $atts, $content, false );
}

/**
 * Login functionality
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content login Content.
 */
function get_sesamy_login( $atts = null, $content = null ) {
	ob_start();
	sesamy_login( $atts, $content );
	return ob_get_clean();
}

/**
 * Login Shortcode attributes
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content Content.
 */
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

	Sesamy_Utils::make_tag( 'sesamy-login', $atts, $content, false );
}

/**
 * Sesamy profile
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content profile Content.
 */
function get_sesamy_profile( $atts = null, $content = null ) {
	ob_start();
	sesamy_profile( $atts, $content );
	return ob_get_clean();
}

/**
 * Profile Attributes
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content profile Content.
 */
function sesamy_profile( $atts = null, $content = null ) {
	$atts = shortcode_atts(
		array(
		// Add here if needed.
		),
		$atts,
		'sesamy_profile'
	);

	Sesamy_Utils::make_tag( 'sesamy-profile', $atts, $content, false );
}

/**
 * Config functions callback
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content config Content.
 */
function get_sesamy_config( $atts = null, $content = null ) {
	ob_start();
	sesamy_config( $atts, $content );
	return ob_get_clean();
}

/**
 * Render the config tag
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content config Content.
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

	Sesamy_Utils::make_tag( 'sesamy-config', $atts, $content, false );
}

/**
 * Render the config tag
 *
 * @since 1.0.0
 * @package    Sesamy
 */
function sesamy_get_enabled_post_types() {
	$content_types = get_option( 'sesamy_content_types', array() );
	return ( $content_types && is_array( $content_types ) ) ? $content_types : array();
}

/**
 * Return information about a pass in sesamy in a easily accessible way
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array $term Term object.
 */
function sesamy_get_pass_info( $term ) {

	$term = get_term( $term, 'sesamy_passes' );

	$image_id = get_term_meta( $term->term_id, 'image_id', true );

	return array(
		'id'            => $term->slug,
		'title'         => $term->name,
		'description'   => $term->description,
		'price'         => get_term_meta( $term->term_id, 'price', true ),
		'currency'      => get_option( 'sesamy_global_currency' ),
		'image'         => ! empty( $image_id ) ? wp_get_attachment_image_url( $image_id, 'full' ) : null,
		'url'           => get_term_meta( $term->term_id, 'url', true ),
		'item_src'      => get_site_url() . '/wp-json/sesamy/v1/passes/' . $term->slug,
		'period'        => get_term_meta( $term->term_id, 'period', true ),
		'time'          => get_term_meta( $term->term_id, 'time', true ),
		'product_type'  => 'PASS',
		'purchase_type' => 'RECURRING',
	);
}

/**
 * Return information about sesamy settings for a post in an easy accessible way
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array $post_id Post ID.
 */
function sesamy_get_post_settings( $post_id ) {
	return Sesamy_Post_Properties::get_post_settings( $post_id );
}

/**
 * Return information about sesamy settings for a post in an easy accessible way
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array $post_id_or_passes Post ID.
 * @param array $separator Seperater.
 */
function sesamy_get_passes( $post_id_or_passes, $separator = ';' ) {

	return sesamy_get_passes_urls( $post_id_or_passes, $separator );
}

/**
 * Get passes URL
 *
 * @since 1.0.0
 * @package    Sesamy
 * @param array $post_id_or_passes Post ID.
 * @param array $separator Seperater.
 */
function sesamy_get_passes_urls( $post_id_or_passes, $separator = ';' ) {
	if ( is_array( $post_id_or_passes ) ) {
		// Array of passes.
		$passes = $post_id_or_passes;
	} elseif ( $post_id_or_passes instanceof WP_Post ) {
		// It's a WP_Post.
		$passes = sesamy_get_post_settings( $post_id_or_passes->ID )['passes'];
	} elseif ( is_numeric( $post_id_or_passes ) ) {
		// A post ID.
		$passes = sesamy_get_post_settings( $post_id_or_passes )['passes'];
	}

	$pass_api_urls = is_array( $passes ) ? array_map(
		function ( $p ) {
			return ! empty( $p['item_src'] ) ? $p['item_src'] : null;
		},
		$passes
	) : array();

	return implode( $separator, $pass_api_urls );
}

/**
 * Get Paywall Wizard HTML
 *
 * @since 2.1.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content Content.
 */
function get_sesamy_paywall_wizard( $atts = null, $content = null ) {
	ob_start();
	sesamy_paywall_wizard( $atts, $content );
	return ob_get_clean();
}

/**
 * Paywall Wizard Shortcode With attributes
 *
 * @since 2.1.0
 * @package    Sesamy
 * @param array  $atts Attributes.
 * @param string $content Content.
 */
function sesamy_paywall_wizard( $atts = null, $content = null ) {
	$atts = shortcode_atts(
		array(
			'publisher_content_id' => '',
			'item_src' => '',
			'settings_url' => '',
		),
		$atts,
		'sesamy_paywall_wizard'
	);
	Sesamy_Utils::make_tag( 'sesamy-paywall-wizard', $atts, $content, false );
}