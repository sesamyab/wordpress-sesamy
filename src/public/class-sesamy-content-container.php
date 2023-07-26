<?php

/**
 * Define the content container logic
 *
 * @link       https://www.viggeby.com
 * @since      1.0.0
 * @author     Jonas Stensved <jonas@viggeby.com>
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

class Sesamy_Content_Container {



	public function process_main_content( $content ) {

		// Using the <!-- more --> will break core if excerpt is empty as this will cause an infite loop
		// See: https://github.com/WordPress/gutenberg/issues/5572#issuecomment-407756810
		if ( doing_filter( 'get_the_excerpt' ) ) {
			return $content;
		}

		// Check if we're inside the main loop for any of the enabled post types
		if ( is_singular( sesamy_get_enabled_post_types() ) && in_the_loop() && is_main_query() ) {

			global $post;

			$link_has_valid_sign = false;

			// Check if current request has a valid signed link
			if ( isset( $_GET['ss'] ) ) {

				global $wp;
				$sesamy_signed_url   = new Sesamy_Signed_Url();
				$current_url         = home_url( add_query_arg( $_GET, $wp->request ) );
				$link_has_valid_sign = ( true === $sesamy_signed_url->is_valid_url( $post, $current_url ) );

			}

			// Apply content container if current url is not signed
			if ( ! $link_has_valid_sign ) {
				return apply_filters( 'sesamy_content', $post, $content );
			}
		}

		return $content;
	}


	public function process_content( $post, $content ) {

		$post_settings = sesamy_get_post_settings( $post->ID );

		$preview = $this->extract_preview( $post );

		$isLocked = Sesamy_Post_Properties::is_locked( $post->ID );

		$atts = array(
			'publisher_content_id' => $post->ID,
			'item_src'             => get_permalink(),
			'preview'              => apply_filters( 'sesamy_paywall_preview', $preview ),
			'pass'                 => sesamy_get_passes_urls( $post_settings['passes'] ),
			'locked'               => $isLocked ? 'true' : 'false',
		);

		$default_paywall = $this->show_paywall( $post, $post_settings );
		$paywall_seo     = apply_filters( 'sesamy_paywall_seo', $this->show_seo_paywall_data( $post ), $post );

		// Check if the post is unlocked. If so, only return the content container.
		if ( !$isLocked ) {
			return $paywall_seo . get_sesamy_content_container( $atts, $content );
		}

		return $paywall_seo . get_sesamy_content_container( $atts, $content ) . apply_filters( 'sesamy_paywall', $default_paywall, $post, $post_settings );
	}

	/**
	 * Extract preview from post with logic to take more-tags into account
	 */
	public function extract_preview( $post ) {

		// If the post isn't locked, we don't need to extract a preview
		if ( ! Sesamy_Post_Properties::is_locked( $post->ID ) ) {
			return null;
		}

		// Caution: WordPress has two blocks, the original "more" and the "read-more". We support the "more" as that is intended for cutting previews.

		// Retrieve content before <!-- more --> if defined, otherwise use get_the_excerpt as default.
		$extended = get_extended( $post->post_content );

		if ( ! empty( $extended['main'] ) && ! empty( $extended['extended'] ) ) {
			return $extended['main'];
		} else {
			return get_the_excerpt();
		}
	}

	/**
	 * Wrap the content container. This is added to a function to be easy to opt-out of with filter
	 * This makes the default theme and probably other themes work more nicely out of the box with sesamy component
	 */
	public function sesamy_content_container_wrap( $content ) {
		return '<div>' . $content . '</div>';
	}

	/**
	 * Add SEO markup to identify paywall content
	 * See: https://developers.google.com/search/docs/appearance/structured-data/paywalled-content
	 */
	public function show_seo_paywall_data( $post ) {
		ob_start();

		$headline       = get_the_title( $post );
		$image          = get_the_post_thumbnail_url( $post, 'full' );
		$date_published = get_the_date( 'c', $post );
		$date_modified  = get_the_modified_date( 'c', $post );
		$author         = get_the_author_meta( 'display_name', $post );
		$description    = get_the_excerpt( $post );
		$isAccessibleForFree   = Sesamy_Post_Properties::is_locked( $post->ID ) ? 'False' : 'True';

		?>
		<script type="application/ld+json">
		{
		"@context": "https://schema.org",
		"@type": "NewsArticle",
		"headline": "<?php echo esc_html( $headline ); ?>",
		"image": "<?php echo esc_html( $image ); ?>",
		"datePublished": "<?php echo esc_html( $date_published ); ?>",
		"dateModified": "<?php echo esc_html( $date_modified ); ?>",
		"author": {
			"@type": "Person",
			"name": "<?php echo esc_html( $author ); ?>"
		},
		"description": "<?php echo esc_html( $description ); ?>",
		"isAccessibleForFree": "<?php echo esc_html( $isAccessibleForFree ); ?>",
		"hasPart":
			{
			"@type": "WebPageElement",
			"isAccessibleForFree": "<?php echo esc_html( $isAccessibleForFree ); ?>",
			"cssSelector" : "sesamy-content-container"
			}
		}
		</script>
		<?php

		return ob_get_clean();
	}

	public function show_paywall( $post, $post_settings ) {

		ob_start();

		?>
		<div class="sesamy-paywall" data-sesamy-paywall data-sesamy-item-src="<?php the_permalink( $post->ID ); ?>" data-sesamy-passes="<?php sesamy_get_passes_urls( $post->ID ); ?>">

		<?php sesamy_login(); ?>
		<?php

		if ( $post_settings['enable_single_purchase'] ) {

			$button_args = array(
				'price'    => $post_settings['price'],
				'currency' => $post_settings['currency'],
				'item_src' => get_the_permalink( $post->ID ),
			);
			sesamy_button( $button_args, null );
		}

		if ( count( $post_settings['passes'] ) > 0 ) {

			foreach ( $post_settings['passes'] as $pass ) {

				$button_args = array(
					'text'                 => $pass['title'],
					'price'                => $pass['price'],
					'currency'             => $pass['currency'],
					'item_src'             => $pass['item_src'],
					'publisher_content_id' => $pass['id'],
				);
				sesamy_button( $button_args, null );
			}
		}
		?>
		</div>
		<style>
			.sesamy-paywall {
				display: flex;
				flex-wrap: wrap;
				gap: 1rem;
				justify-content: center;
			}
		</style>
		<?php

		return ob_get_clean();
	}
}
