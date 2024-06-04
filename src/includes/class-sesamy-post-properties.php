<?php
/**
 * Post properties
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * Taxonomy and add form fields.
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Post_Properties {
	/**
	 * Register post meta
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 */
	public function register_post_meta() {
		$post_types = sesamy_get_enabled_post_types();

		if ( $post_types ) {
			foreach ( $post_types as $post_type ) {

				register_post_meta(
					$post_type,
					'_sesamy_locked',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'boolean',
						'default'       => false,
						'auth_callback' => '__return_true',
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_enable_single_purchase',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'boolean', // string to allow empty.
						'auth_callback' => '__return_true',
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_price',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'string', // string to allow empty.
						'auth_callback' => '__return_true',
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_currency',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'string',
						'auth_callback' => '__return_true',
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_locked_from',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'integer',
						'auth_callback' => '__return_true',
						'default'       => -1,
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_locked_until',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'integer',
						'auth_callback' => '__return_true',
						'default'       => -1,
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_access_level',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'string',
						'auth_callback' => '__return_true',
						'default'       => 'entitlement',
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_tags',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'string',
						'auth_callback' => '__return_true',
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_show_login',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'boolean',
						'default'       => false,
						'auth_callback' => '__return_true',
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_paywall_wizard',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'boolean',
						'default'       => false,
						'auth_callback' => '__return_true',
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_paywall_wizard_logo_url',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'string',
						'auth_callback' => '__return_true',
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_paywall_wizard_title',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'string',
						'auth_callback' => '__return_true',
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_paywall_wizard_description',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'string',
						'auth_callback' => '__return_true',
					)
				);

				register_post_meta(
					$post_type,
					'_sesamy_paywall_wizard_perks',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'string',
						'auth_callback' => '__return_true',
					)
				);

			}
		}
	}

	/**
	 * Price info
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $tier term Object.
	 */
	public static function get_tier_price_info( $tier ) {

		return array(
			'type'     => get_term_meta( $tier->term_id, 'type', true ),
			'price'    => get_term_meta( $tier->term_id, 'price', true ),
			'currency' => get_option( 'sesamy_global_currency' ),
		);
	}

	/**
	 * Locked post check
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $post post Object.
	 */
	public static function is_locked( $post ) {

		$post = get_post( $post );
		return (bool) ( get_post_meta( $post->ID, '_sesamy_locked', true ) ?? false );
	}

	/**
	 * Locked post check
	 *
	 * @since  2.1.0
	 * @package    Sesamy
	 * @param array $post post Object.
	 */
	public static function is_paywall_wizard( $post ) {

		$post = get_post( $post );
		return (bool) ( get_post_meta( $post->ID, '_sesamy_paywall_wizard', true ) ?? false );
	}

	/**
	 * Returns post price info with applied ruleset and logic
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $post post Object.
	 */
	public static function get_post_price_info( $post ) {

		$post = get_post( $post );

		$info = array(
			'enable_single_purchase' => null,
			'price'                  => null,
			'currency'               => null,
		);

		$info['enable_single_purchase'] = boolval( get_post_meta( $post->ID, '_sesamy_enable_single_purchase', true ) );
		$info['price']                  = get_post_meta( $post->ID, '_sesamy_price', true );
		$info['currency']               = get_option( 'sesamy_global_currency' );

		return $info;
	}

	/**
	 * Post passes
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $post post Object.
	 */
	public static function get_post_passes( $post ) {

		$post = get_post( $post );
		return get_the_terms( $post, 'sesamy_passes' );
	}

	/**
	 * Return information about sesamy settings for a post in an easy accessible way
	 *
	 * @param  int $post_id post ID.
	 * @return Array
	 */
	public static function get_post_settings( $post_id ) {
		$post   = get_post( $post_id );
		$passes = get_the_terms( $post->ID, 'sesamy_passes' );

		return array(
			'locked'                 	=> (bool) get_post_meta( $post->ID, '_sesamy_locked', true ),
			'enable_single_purchase' 	=> (bool) get_post_meta( $post->ID, '_sesamy_enable_single_purchase', true ),
			'price'                  	=> get_post_meta( $post->ID, '_sesamy_price', true ),
			'passes'                 	=> is_array( $passes ) ? array_map( 'sesamy_get_pass_info', $passes ) : array(),
			'locked_from'            	=> (int) get_post_meta( $post->ID, '_sesamy_locked_from', true ),
			'locked_until'           	=> (int) get_post_meta( $post->ID, '_sesamy_locked_until', true ),
			'sesamy_tags'            	=> get_post_meta( $post->ID, '_sesamy_tags', true ),
			'access_level'           	=> get_post_meta( $post->ID, '_sesamy_access_level', true ),
			'paywall_wizard'         	=> (bool) get_post_meta( $post->ID, '_sesamy_paywall_wizard', true ),
			'paywall_wizard_logo_url'   => get_post_meta( $post->ID, '_sesamy_paywall_wizard_logo_url', true ),
			'paywall_wizard_title'   	=> get_post_meta( $post->ID, '_sesamy_paywall_wizard_title', true ),
			'paywall_wizard_description'=> get_post_meta( $post->ID, '_sesamy_paywall_wizard_description', true ),
			'paywall_wizard_perks'      => get_post_meta( $post->ID, '_sesamy_paywall_wizard_perks', true ),
			'show_login' 				=> get_post_meta( $post->ID, '_sesamy_show_login', true ),
		);
	}
}