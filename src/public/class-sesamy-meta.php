<?php

/**
 * Define the shortcodes
 *
 *
 * @link       https://www.viggeby.com
 * @since      1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * Define the shortcodes
 *
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

			if ( in_array( $post->post_type, ['post', 'artiklar'] ) ) {


				echo $this->make_tag( 'sesamy:title', $post->post_title );
				echo $this->make_tag( 'sesamy:description', $post->post_excerpt );
				echo $this->make_tag( 'sesamy:image', get_the_post_thumbnail_url( $post ) );
				echo $this->make_tag( 'sesamy:price', "10" );
				echo $this->make_tag( 'sesamy:currency ', "SEK" );
			}
		}

	}

	/**
	 * Generate tag
	 */
	function make_tag( $name , $content){		
		$content  = strip_tags( $content );
		return '<meta property="' . $name . '" content="' . $content . '" />' . "\n";
	}


}
