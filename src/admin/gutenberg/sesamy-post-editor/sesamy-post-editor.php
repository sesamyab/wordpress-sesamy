<?php
/**
 * Plugin Name:       Sesamy
 * Description:       Options for Sesamy
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sesamy-post-editor
 *
 * @package           sesamy-post-editor
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function sesamy_post_editor_sesamy_post_editor_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'sesamy_post_editor_sesamy_post_editor_block_init' );
