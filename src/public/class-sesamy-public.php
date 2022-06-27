<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.viggeby.com
 * @since      1.0.0
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
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
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
	 * @since    1.0.0
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sesamy-public.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( 'sesamy-content-container', 'https://assets.sesamy.dev/scripts/checkout-button/sesamy-content-container.min.js', array(), $this->version, true );
		wp_enqueue_script( 'sesamy-button-container', 'https://assets.sesamy.dev/scripts/checkout-button/sesamy-button-container.min.js', array(), $this->version, true );
		wp_enqueue_script( 'sesamy-button', 'https://assets.sesamy.dev/scripts/checkout-button/sesamy-button.min.js', array(), $this->version, true );
		wp_enqueue_script( 'sesamy-login', 'https://assets.sesamy.dev/scripts/checkout-button/sesamy-login.min.js', array(), $this->version, true );
		
	}


	/**
	 * Register shortcodes
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {

		$shortcodes = new Sesamy_Shortcodes();
		$shortcodes->register();
	}

	/**
	 *  Wrap main content with sesamy content container
	 */
	public function register_container_logic() {

		add_filter( 'the_content', 'filter_the_content_in_the_main_loop', 1 );
 
		function filter_the_content_in_the_main_loop( $content ) {
		
			// Check if we're inside the main loop in a single Post.
			if ( is_singular() && in_the_loop() && is_main_query() ) {
				return $content . esc_html__( 'I’m filtering the content inside the main loop', 'wporg');
			}
		
			return $content;
		}

	}

}
