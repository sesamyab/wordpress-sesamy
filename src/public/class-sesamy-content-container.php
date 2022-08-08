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
	
		// Using the <!-- more --> will break core if excerpt is empty as this will cause an infite loop
		// See: https://github.com/WordPress/gutenberg/issues/5572#issuecomment-407756810
		if ( doing_filter( 'get_the_excerpt' ) ) {
			return $content;
		}

		// Check if we're inside the main loop for any of the enabled post types
		if ( is_singular( sesamy_get_enabled_post_types() ) && in_the_loop() && is_main_query() ) {

				global $post;	

				$locked = get_post_meta( $post->ID, '_sesamy_locked', true);

				if( $locked ){
					return apply_filters( 'sesamy_content', $content );
				}
		}
	
		return $content;
	}


	function process_content( $content ) {

		

		$atts = [
			'item_src' => get_permalink(),
			'preview' => get_the_excerpt()
		];

		return sesamy_content_container( $atts, $content) . sesamy_button( $atts, '');

	}
}
