<?php

/**
 * Define the content container logic
 *
 *
 * @link       https://www.viggeby.com
 * @since      1.0.0
 * @author     Jonas Stensved <jonas@viggeby.com>
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

class Sesamy_ContentContainer {

	function process_main_content( $content ) {
	
		// Check if we're inside the main loop in a single Post.
		if ( is_singular() && in_the_loop() && is_main_query() ) {

			return apply_filters( 'sesamy_content', $content );
		}
	
		return $content;
	}


	function process_content( $content ) {
	
		global $post;

		// TODO: Add filter for post types to cover from settings

		if ( in_array( $post->post_type, sesamy_get_enabled_post_types() ) ) {

			$atts = [
				'item_src' => get_permalink(),
				'preview' => get_the_excerpt()
			];

			// Todo: Apply protection based on ruleset
			return sesamy_content_container( $atts, $content) . sesamy_button( $atts, '');

		}

		return $content;
	}
}
