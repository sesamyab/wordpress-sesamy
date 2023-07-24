<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Sesamy
 * @subpackage Sesamy/public
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Public {


	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sesamy_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sesamy_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sesamy-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sesamy_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sesamy_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'sesamy-scripts', Sesamy::$instance->get_assets_url() . '/scripts/web-components/sesamy-bundle.min.js', array(), $this->version, true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sesamy-public.js', array( 'jquery', 'sesamy-scripts' ), $this->version, true );
	}


	/**
	 * Register shortcodes
	 *
	 * @since 1.0.0
	 */
	public function register_shortcodes() {

		$shortcodes = new Sesamy_Shortcodes();
		$shortcodes->register();
	}

	/**
	 * Transition post status
	 * Fires a webhook to Sesamy when a post is published
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {

		// Only run for enabled post types
		if ( ! in_array( $post->post_type, sesamy_get_enabled_post_types(), true ) ) {
			return;
		}

		// Only run when post is published
		if ( 'publish' === $new_status && 'publish' !== $old_status ) {

			// Get the client id
			$client_id = get_option( 'sesamy_client_id' );

			// Bail if no client id is set
			if ( empty( $client_id ) ) {
				return;
			}

			// Get the post url
			$post_url = get_permalink( $post->ID );

			// Build the request
			$request = array(
				'url' => $post_url,
			);

			// Send the request
			$response = wp_remote_post(
				'https://api.sesamy.com/suppliers/hooks/article/' . $client_id,
				array(
					'headers' => array(
						'Content-Type' => 'application/json',
					),
					'body'    => wp_json_encode( $request ),
				)
			);

			// If the response code is not 200, log an error.
			if ( $response['response']['code'] !== 200 ) {
				error_log( 'Sesamy: Failed to send webhook');
				error_log(print_r($response, true));
			}
		}
	}


}
