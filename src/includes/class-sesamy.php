<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy {



	/**
	 * The instance of the plugin
	 */
	public static $instance;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    Sesamy_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string    $version    The current version of the plugin.
	 */
	protected $version;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( defined( 'SESAMY_VERSION' ) ) {
			$this->version = SESAMY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'sesamy';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_common_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sesamy_Loader. Orchestrates the hooks of the plugin.
	 * - Sesamy_I18n. Defines internationalization functionality.
	 * - Sesamy_Admin. Defines all hooks for the admin area.
	 * - Sesamy_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sesamy-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sesamy-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sesamy-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sesamy-public.php';

		/**
		 * Sesamy includes
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sesamy-utils.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sesamy-passes.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sesamy-post-properties.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sesamy-signed-url.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sesamy-api-endpoint.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sesamy-currencies.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sesamy-settings.php';

		/**
		 * The following classes contains sesamy public
		 */

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sesamy-shortcodes.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sesamy-content-container.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sesamy-meta.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/sesamy-global-functions.php';

		/**
		 * Admin includes
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sesamy-settings-admin.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sesamy-post-editor.php';

		$this->loader = new Sesamy_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sesamy_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {

		$plugin_i18n = new Sesamy_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}


	private function define_common_hooks() {

		$this->loader->add_action( 'init', Sesamy_Passes::get_instance(), 'register_taxonomy' );

		$post_properties = new Sesamy_Post_Properties();
		$this->loader->add_action( 'init', $post_properties, 'register_post_meta' );
		$this->loader->add_action( 'admin_init', $post_properties, 'register_post_meta' );
		$this->loader->add_action( 'rest_api_init', $post_properties, 'register_post_meta' );

		$ep = new Sesamy_Api_Endpoint();
		$this->loader->add_action( 'rest_api_init', $ep, 'register_route' );
		$this->loader->add_filter( 'rest_pre_serve_request', $ep, 'format_response', 10, 4 );

		$currencies = new Sesamy_Currencies();
		$this->loader->add_action( 'rest_api_init', $currencies, 'register_route' );

		$settings = new Sesamy_Settings();
		$this->loader->add_action( 'init', $settings, 'register_settings' );
		$this->loader->add_action( 'rest_api_init', $settings, 'register_route' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Sesamy_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'init', $plugin_admin, 'init' );

		$this->loader->add_action( 'admin_init', Sesamy_Passes::get_instance(), 'admin_init' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {

		$plugin_public = new Sesamy_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );

		// If the lock mode is set to 'none' we should do nothing with the content.
		if ( get_option( 'sesamy_lock_mode' ) !== 'none' ) {

			$sesamy_content_container = new Sesamy_Content_Container();
			$this->loader->add_filter( 'sesamy_content', $sesamy_content_container, 'process_content', 999, 2 );
			$this->loader->add_filter( 'sesamy_content_container', $sesamy_content_container, 'sesamy_content_container_wrap', 10, 1 );

			// Make sure we process sesamy after all other hooks with order 999.
			$this->loader->add_filter( 'the_content', $sesamy_content_container, 'process_main_content', 999 );

		}

		// Make sure we process sesamy after all other hooks with order 999.
		$sesamy_meta = new Sesamy_Meta();
		$this->loader->add_filter( 'wp_head', $sesamy_meta, 'add_meta_tags' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since  1.0.0
	 * @return string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since  1.0.0
	 * @return Sesamy_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}


	/**
	 * Return URL for assets based on admin settings
	 */
	public function get_assets_url() {
		$ep = get_option( 'sesamy_api_endpoint' );
		return ( 'production' === $ep ) ? 'https://assets.sesamy.com' : 'https://assets.sesamy.dev';
	}

}
