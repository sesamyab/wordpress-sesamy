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

		if ($post->post_type == 'post') {

			// Todo: Apply protection based on ruleset

			return '<sesamy-content-container item-src="' . get_permalink() . '">' . $content . '</sesamy-content-container><sesamy-button-container><sesamy-button item-src="' . get_permalink() . '"></sesamy-button></sesamy-button-container>';

		}

		return $content;
	}



}
