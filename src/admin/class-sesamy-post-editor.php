<?php

class Sesamy_Post_Editor {



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

				wp_localize_script( 'sesamy-post-editor', 'sesamy_block_obj',
					array( 
						'home' => get_site_url(),
					)
				);
			}
		);
	}


}
