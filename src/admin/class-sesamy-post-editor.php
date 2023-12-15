<?php
/**
 * The admin post editor functionality of the plugin.
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/admin
 */

/**
 * The admin post editor functionality of the plugin.
 *
 * Added build js file for post editor.
 *
 * @package    Sesamy
 * @subpackage Sesamy/admin
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Post_Editor {
	/**
	 * WP editor enque file
	 *
	 * @package    Sesamy
	 * @since 1.0.0
	 */
	public function init() {

		add_action(
			'enqueue_block_editor_assets',
			function () {

				wp_enqueue_script(
					'sesamy-post-editor',
					plugin_dir_url( __FILE__ ) . '/gutenberg/sesamy-post-editor/build/index.js',
					array( 'wp-plugins', 'wp-edit-post', 'wp-element' ),
					Sesamy::$instance->get_version(),
					false
				);

				wp_localize_script(
					'sesamy-post-editor',
					'sesamy_block_obj',
					array(
						'home' => get_site_url(),
					)
				);
			}
		);
	}
}
