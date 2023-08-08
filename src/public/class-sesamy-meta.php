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


	/**
	 * Render sesamy meta_tags in head tag
	 *
	 * @return void
	 */
	public function add_meta_tags() {

		// client-id should be present on all pages
		$this->make_tag( 'sesamy:client-id', get_option( 'sesamy_client_id', '' ) );

		if ( is_singular() ) {
			global $post;

			if ( in_array( $post->post_type, sesamy_get_enabled_post_types(), true ) ) {

				$price_info = Sesamy_Post_Properties::get_post_price_info( $post );

				$this->make_tag( 'sesamy:title', $post->post_title );
				$this->make_tag( 'sesamy:description', wp_strip_all_tags( $post->post_excerpt ) );
				$this->make_tag( 'sesamy:image', get_the_post_thumbnail_url( $post ) );
				$this->make_tag( 'sesamy:price', $price_info['price'] );
				$this->make_tag( 'sesamy:currency', $price_info['currency'] );
				$this->make_tag( 'sesamy:publisher-content-id', $post->ID );
				$this->make_tag( 'sesamy:pass', sesamy_get_passes_urls( $post ) );
			}
		}
	}

	/**
	 * Generate meta tag
	 *
	 * @param string $name
	 * @param string $content
	 * @return void
	 */
	public function make_tag( $name, $content ) {
		$content = wp_strip_all_tags( $content );
		echo '<meta property="' . esc_attr( $name ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
	}


}
