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

					$link_has_valid_sign = false;

					// Check if current request has a valid signed link
					if ( isset( $_GET['ss']) ) {

						global $wp;
						$current_url = home_url( add_query_arg( $_GET, $wp->request ) );
						$signed_url = new Sesamy_Signed_Url();
	
						$link_has_valid_sign = ( TRUE === $signed_url->is_valid_link( $current_url ) );

					}

					// Apply content container if current url is not signed
					if ( !$link_has_valid_sign ) {
						return apply_filters( 'sesamy_content', $post, $content );
					}
					
				}
		}
	
		return $content;
	}

	
	function process_content( $post, $content ) {

		$post_settings = sesamy_get_post_settings($post->ID);

		// Retrieve content before <!-- more -->
		$extended = get_extended( $post->post_content );

		// If all content is in main and nothing in extended, then no <!-- more --> is provided, default to get_the_excerpt() to avoid showing all text
		$preview = ( !empty( $extended['main'] ) && !empty( $extended['extended'] ) ) ? $extended['main'] : get_the_excerpt();

		$atts = [
			'publisher_content_id' 	=> $post->ID,
			'item_src' 				=> get_permalink(),
			'preview'				=> $preview,
			'pass'					=> sesamy_get_passes( $post_settings['passes'] ) // count() > 0 ? $post_settings['passes'][0]['item_src'] : '' // TODO: Multiple?
		];

		

		$default_paywall = $this->show_paywall( $post, $post_settings );
		$paywall_seo = apply_filters( 'sesamy_paywall_seo', $this->show_seo_paywall_data( $post ), $post );

		// Note: The wrapping div makes the container inherit sizes and margins by default in most themes compared to raw webpart
		return  $paywall_seo  . '<div>' . sesamy_content_container( $atts, $content) . '</div>' .  apply_filters( 'sesamy_paywall', $default_paywall, $post, $post_settings);

	}

	/**
	 * Add SEO markup to identify paywall content
	 * See: https://developers.google.com/search/docs/appearance/structured-data/paywalled-content
	 */
	function show_seo_paywall_data( $post )
	{
		ob_start();

		$headline = get_the_title( $post);
		$image = get_the_post_thumbnail_url( $post, 'full' );
		$datePublished = get_the_date( 'c', $post );
		$dateModified = get_the_modified_date( 'c', $post );
		$author = get_the_author_meta( 'display_name', $post );
		$description = get_the_excerpt( $post);

		
		?>
		<script type="application/ld+json">
		{
		"@context": "https://schema.org",
		"@type": "NewsArticle",
		"headline": "<?php  echo $headline; ?>,
		"image": "<?php echo $image; ?>",
		"datePublished": "<?php echo $datePublished; ?>",
		"dateModified": "<?php echo $dateModified; ?>",
		"author": {
			"@type": "Person",
			"name": "<?php echo $author; ?>"
		},
		"description": "<?php echo $description; ?>"",
		"isAccessibleForFree": "False",
		"hasPart":
			{
			"@type": "WebPageElement",
			"isAccessibleForFree": "False",
			"cssSelector" : "sesamy-content-container"
			}
		}
		</script>
		<?php

		return ob_get_clean();
	}

	function show_paywall( $post, $post_settings){

		ob_start();
		
		?>
		<div class="sesamy-paywall" data-sesamy-paywall data-sesamy-item-src="<?php echo get_the_permalink( $post->ID ); ?>" data-sesamy-passes="<?php  sesamy_get_passes( $post->ID ) ?>">
			
			<?php echo sesamy_login(); ?>
			<?php

				if ($post_settings['enable_single_purchase']){

					$button_args = [
						'price' 		=> $post_settings['price'], 
						'currency' 		=> $post_settings['currency'],
						'item_src'		=> get_the_permalink($post->ID)
					];
					echo sesamy_button($button_args, '');
				}

				if( !empty($post_settings['passes']) && count($post_settings['passes']) > 0 ){
					
					foreach($post_settings['passes'] as $pass){

						$button_args = [
							'text' 					=> $pass['title'], 
							'price' 				=> $pass['price'], 
							'currency' 				=> $pass['currency'],
							'item_src' 				=> $pass['url'],
							'publisher_content_id'	=> $pass['id']
						];
						echo sesamy_button($button_args, '');
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
