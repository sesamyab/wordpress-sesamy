<?php

/**
 * Define the shortcodes
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * Define the shortcodes
 *
 * @since      1.0.0
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Meta {



	function add_meta_tags() {

		if ( is_singular() ) {
			global $post;

			if ( in_array( $post->post_type, sesamy_get_enabled_post_types(), true ) && Sesamy_Post_Properties::is_locked( $post ) ) {

				$price_info = Sesamy_Post_Properties::get_post_price_info( $post );

				echo $this->make_tag( 'sesamy:title', $post->post_title );
				echo $this->make_tag( 'sesamy:description', wp_strip_all_tags( $post->post_excerpt ) );
				echo $this->make_tag( 'sesamy:image', get_the_post_thumbnail_url( $post ) );
				echo $this->make_tag( 'sesamy:price', $price_info['price'] );
				echo $this->make_tag( 'sesamy:currency', $price_info['currency'] );
				echo $this->make_tag( 'sesamy:client-id', get_option( 'sesamy_client_id', '' ) );
				echo $this->make_tag( 'sesamy:publisher-content-id', $post->ID );
				echo $this->make_tag( 'sesamy:pass', sesamy_get_passes( $post ) );
			}
		}

	}

	/**
	 * Generate tag
	 */
	function make_tag( $name, $content ) {
		$content = strip_tags( $content );
		return '<meta property="' . $name . '" content="' . $content . '" />' . "\n";
	}


}
