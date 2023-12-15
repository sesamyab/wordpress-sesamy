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
	 * Add meta tags
	 *
	 * @since 1.0.0
	 * @package    Sesamy
	 * @return void
	 */
	public function add_meta_tags() {

		// client-id should be present on all pages.
		$this->make_tag( 'sesamy:client-id', get_option( 'sesamy_client_id', '' ) );

		if ( is_singular() ) {
			global $post;

			if ( in_array( $post->post_type, sesamy_get_enabled_post_types(), true ) ) {

				$price_info = Sesamy_Post_Properties::get_post_price_info( $post );

				$this->make_tag( 'sesamy:title', $post->post_title );
				$this->make_tag( 'sesamy:description', wp_strip_all_tags( $post->post_excerpt ) );
				$this->make_tag( 'sesamy:image', get_the_post_thumbnail_url( $post ) );
				$this->make_tag( 'sesamy:price', $price_info['price'] );
				$this->make_tag( 'sesamy:currency', get_option( 'sesamy_gloabl_currency' ) );
				$this->make_tag( 'sesamy:publisher-content-id', $post->ID );
				$this->make_tag( 'sesamy:pass', sesamy_get_passes_urls( $post ) );

				// Published time.
				$this->make_Tag( 'sesamy:published_time', get_the_date( 'Y-m-d\TH:i:s\Z', $post->ID ) );

				// Get the name of the primary category (Yoast SEO) and set the sesamy:section tag.
				if ( function_exists( 'yoast_get_primary_term_id' ) ) {
					$primary_term = yoast_get_primary_term_id( 'category', $post->ID );
					if ( $primary_term ) {
						$term = get_term( $primary_term, 'category' );
						if ( $term ) {
							$sesamy_primary_category = apply_filters( 'sesamy_post_primary_category', $term->name, $post );
							if ( ! empty( $sesamy_primary_category ) ) {
								$this->make_tag( 'sesamy:section', $sesamy_primary_category );
								$this->make_tag( 'article:section', $sesamy_primary_category );
							}
						}
					}
				}

				// Loop through all the taxonomies and set the sesamy:tags tag.
				$taxonomies = get_object_taxonomies( $post->post_type, 'objects' );
				$tags = array();
				foreach ( $taxonomies as $taxonomy ) {
					$terms = get_the_terms( $post->ID, $taxonomy->name );
					if ( $terms && ! is_wp_error( $terms ) ) {
						foreach ( $terms as $term ) {

							// Check that it's not the "uncategorized" category.
							if ( 'category' === $term->taxonomy && 1 === $term->term_id ) {
								continue;
							}

							$tags[] = $term->name;
						}
					}
				}

				$tags = apply_filters( 'sesamy_post_tags', $tags, $post );

				if ( ! empty( $tags ) ) {
					foreach ( $tags as $tag ) {
						$this->make_tag( 'sesamy:tag', $tag );
						$this->make_tag( 'article:tag', $tag );
					}
				}
			}
		}
	}

	/**
	 * Generate meta tag
	 *
	 * @param string $name Meta name.
	 * @param string $content Meta content.
	 * @return void
	 */
	public function make_tag( $name, $content ) {
		$content = wp_strip_all_tags( $content );
		echo '<meta property="' . esc_attr( $name ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
	}
}
