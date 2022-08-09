<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.viggeby.com
 * @since             1.0.0
 * @package           Sesamy
 *
 * @wordpress-plugin
 * Plugin Name:       Sesamy
 * Plugin URI:        https://sesamy.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Sesamy, Jonas Stensved
 * Author URI:        https://www.viggeby.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sesamy
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SESAMY_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sesamy-activator.php
 */
function activate_sesamy() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sesamy-activator.php';
	Sesamy_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sesamy-deactivator.php
 */
function deactivate_sesamy() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sesamy-deactivator.php';
	Sesamy_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sesamy' );
register_deactivation_hook( __FILE__, 'deactivate_sesamy' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sesamy.php';

/**
 * Include composer packages
 */
require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

Sesamy::$instance = new Sesamy();
Sesamy::$instance->run();
