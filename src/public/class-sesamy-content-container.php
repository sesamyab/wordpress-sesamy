<?php
/**
 * Define the content container logic
 *
 * @link       https://www.viggeby.com
 * @since      1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * Define the content container logic
 *
 * @since      1.0.0
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Content_Container {
	/**
	 * Process Main Content
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $content Post Content.
	 */
	public function process_main_content( $content ) {

		// Using the <!-- more --> will break core if excerpt is empty as this will cause an infite loop.
		// See: https://github.com/WordPress/gutenberg/issues/5572#issuecomment-407756810.
		if ( doing_filter( 'get_the_excerpt' ) ) {
			return $content;
		}

		// Check if we're inside the main loop for any of the enabled post types.
		if ( is_singular( sesamy_get_enabled_post_types() ) && in_the_loop() && is_main_query() ) {
			global $post;
			return apply_filters( 'sesamy_content', $post, $content );
		}

		return $content;
	}

	/**
	 * Post excerpt change based on settings.
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $post Post Object.
	 * @param array $content Post Content.
	 */
	public function process_content( $post, $content ) {

		$post_settings = sesamy_get_post_settings( $post->ID );

		$preview = $this->extract_preview( $post );

		$is_locked = Sesamy_Post_Properties::is_locked( $post->ID );

		if ( $post_settings['access_level'] != -1 && !empty( $post_settings['access_level'] ) ) {
			$access_level = $post_settings['access_level'];	
		} else {
			$access_level = 'entitlement';
		}

		$atts = array(
			'publisher_content_id' => $post->ID,
			'item_src'             => get_permalink(),
			'preview'              => apply_filters( 'sesamy_paywall_preview', $preview ),
			'pass'                 => sesamy_get_passes_urls( $post_settings['passes'] ),
			'locked'               => $is_locked ? 'true' : 'false',
		);

		$default_paywall = $this->show_paywall( $post, $post_settings );
		$paywall_seo     = apply_filters( 'sesamy_paywall_seo', $this->show_seo_paywall_data( $post ), $post );

		$lock_mode = get_option( 'sesamy_lock_mode' );

		// Check if the post is unlocked or if the access level is public. If so, only return the content container.
		$is_public = !$is_locked || $access_level === 'public' || $lock_mode === 'none';
		if ( $is_public ) {
			return $paywall_seo . get_sesamy_content_container( $atts, $content );
		}

		return $paywall_seo . get_sesamy_content_container( $atts, $content ) . apply_filters( 'sesamy_paywall', $default_paywall, $post, $post_settings );
	}

	/**
	 * Extract preview from post with logic to take more-tags into account
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $post Post Object.
	 */
	public function extract_preview( $post ) {

		// If the post isn't locked, we don't need to extract a preview.
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
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $content Post Content.
	 */
	public function sesamy_content_container_wrap( $content ) {
		return '<div>' . $content . '</div>';
	}

	/**
	 * Add SEO markup to identify paywall content
	 * See: https://developers.google.com/search/docs/appearance/structured-data/paywalled-content
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $post Post Object.
	 */
	public function show_seo_paywall_data( $post ) {
		ob_start();

		$headline            = get_the_title( $post );
		$image               = get_the_post_thumbnail_url( $post, 'full' );
		$date_published      = get_the_date( 'c', $post );
		$date_modified       = get_the_modified_date( 'c', $post );
		$author              = get_the_author_meta( 'display_name', $post );
		$description         = get_the_excerpt( $post );
		$isaccessible_forfree = Sesamy_Post_Properties::is_locked( $post->ID ) ? 'False' : 'True';

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
		"isAccessibleForFree": "<?php echo esc_html( $isaccessible_forfree ); ?>",
		"hasPart":
			{
			"@type": "WebPageElement",
			"isAccessibleForFree": "<?php echo esc_html( $isaccessible_forfree ); ?>",
			"cssSelector" : "sesamy-content-container"
			}
		}
		</script>
		<?php

		return ob_get_clean();
	}

	/**
	 * Display Paywall
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $post Post Object.
	 * @param array $post_settings Settings Array.
	 * @return Paywall html
	 */
	public function show_paywall( $post, $post_settings ) {

		ob_start();

		?>
		<div class="sesamy-paywall" data-sesamy-paywall data-sesamy-item-src="<?php the_permalink( $post->ID ); ?>" data-sesamy-passes="<?php sesamy_get_passes_urls( $post->ID ); ?>">

		<?php
		// Setup the paywall wizard if enabled
		if ( $post_settings['paywall_wizard'] ) {
			// Get the default Paywall Wizard values
			$logo_url = get_option( 'sesamy_paywall_wizard_logo_url' );
			$title    = get_option( 'sesamy_paywall_wizard_title' );
			$single_purchase_description = get_option( 'sesamy_paywall_wizard_description' );
			$perks    = get_option( 'sesamy_paywall_wizard_perks' );

			// Override the default values if the post has custom values
			if ( 1 == $post_settings['paywall_wizard_override_default'] ) {
				$logo_url                    = $post_settings['paywall_wizard_logo_url'];
				$title                       = $post_settings['paywall_wizard_title'];
				$single_purchase_description = $post_settings['paywall_wizard_description'];
				$perks                       = $post_settings['paywall_wizard_perks'];
			}

			// Format the perks to be used in the paywall wizard.
			$perks_array             = explode( "\n", $perks );
			$perks_array_with_quotes = array_map(
				function( $perk ) {
					return '"' . trim( $perk ) . '"';
				},
				$perks_array
			);
			$separated_perks         = implode( ', ', $perks_array_with_quotes );
			?>
		
		<!-- Paywall wizard inline JSON config -->
		<script type="application/json" id="paywall-wizard">
			{
				"data": {
					"showLoginButton": <?php echo $post_settings['show_login'] ? 'true' : 'false'; ?>,
					"logoSrc": <?php echo json_encode( $logo_url ); ?>,
					"color": "#D62802",
					"vendorId": "<?php echo get_option( 'sesamy_client_id' ); ?>",
					<?php if ( $post_settings['enable_single_purchase'] ) { ?>
					"singlePurchase": {
						"type": "single",
						"text": "<?php echo $post->post_title; ?>",
						"description": <?php echo json_json_encode($single_purchase_description); ?>
					},
					<?php } ?>
					<?php
					if ( count( $post_settings['passes'] ) > 0 ) {
						$lastPass = end( $post_settings['passes'] )
						?>
					"subscriptions": [
						<?php foreach ( $post_settings['passes'] as $pass ) { ?>
						{
							"type": "<?php echo $pass['period']; ?>",
							"text": "<?php echo $pass['title']; ?>",
							"description": <?php echo json_encode($pass['description']); ?>,
							"price": "<?php echo $pass['price']; ?>",
							"currency": "<?php echo $pass['currency']; ?>",
							"recurringPaymentText": "<?php echo $pass['period']; ?>",
							"itemSrc": "<?php echo $pass['item_src']; ?>",
							"po": "",
							"discountCodes": []
						}
							<?php
							if ( $pass !== $lastPass ) {
								echo ',';
							}
						}
						?>
					],
						<?php } ?>
					"subscriptionFeatures": [<?php echo htmlspecialchars_decode( $separated_perks ); ?>],
					"subscriptionPurchaseText": "<?php echo $title; ?>"
				}
			}
		</script>
			<?php
			$paywall_wizard_args = array(
				'publisher_content_id' => $post->ID,
				'item_src'             => get_the_permalink( $post->ID ),
			);
			// Display the paywall wizard.
			sesamy_paywall_wizard( $paywall_wizard_args );
		} else {
			// Show the login button if enabled.
			if ( $post_settings['show_login'] ) {
				sesamy_login();
			}

			// Display the default paywall (single purhcase + passes buttons).
			if ( $post_settings['enable_single_purchase'] ) {
	
				$button_args = array(
					'price'    => $post_settings['price'],
					'currency' => get_option( 'sesamy_global_currency' ),
					'item_src' => get_the_permalink( $post->ID ),
				);
				sesamy_button( $button_args, null );
			}
			if ( count( $post_settings['passes'] ) > 0 ) {
	
				foreach ( $post_settings['passes'] as $pass ) {
	
					$button_args = array(
						'text'                 => $pass['title'],
						'price'                => $pass['price'],
						'currency'             => get_option( 'sesamy_global_currency' ),
						'item_src'             => $pass['item_src'],
						'publisher_content_id' => $pass['id'],
					);
					sesamy_button( $button_args, null );
				}
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
			sesamy-paywall-wizard {
				width: 100%;
				border-radius: 30px;
    			padding: 18px 36px;
				box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px !important;
			}
		</style>
		<?php

		return ob_get_clean();
	}
}