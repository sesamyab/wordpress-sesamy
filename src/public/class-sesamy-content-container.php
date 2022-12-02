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

	function get_pass_names($passes){
		$pass_names = array_map(function($p) { 
			return !empty($p['id']) ? $p['id'] : null; 
		}, $passes); 

		return implode(',', $pass_names);
	}

	function process_content( $post, $content ) {

		$post_settings = sesamy_get_post_settings($post->ID);

		$atts = [
			'publisher_content_id' 	=> $post->ID,
			'item_src' 				=> get_permalink(),
			'preview'				=> get_the_excerpt(),
			'pass'					=> $this->get_pass_names( $post_settings['passes'] ) // count() > 0 ? $post_settings['passes'][0]['item_src'] : '' // TODO: Multiple?
		];

		

		$default_paywall = $this->show_paywall($post, $post_settings);

		return '<div>' . sesamy_content_container( $atts, $content) . '</div> '. apply_filters( 'sesamy_paywall', $default_paywall, $post, $post_settings);

	}


	function show_paywall( $post, $post_settings){

		ob_start();
		
		?>
		<div class="sesamy-paywall">
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
							'text' 					=> $pass['title'] . " / month", 
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
			}
		</style>
		<?php

		return ob_get_clean();

	}
}
