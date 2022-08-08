<?php

class Sesamy_Post_Editor {


    function init(){

        add_action( 'enqueue_block_editor_assets', function() {
            wp_enqueue_script(
                'awp-custom-meta-plugin', 
                plugin_dir_url( __FILE__ ) . '/gutenberg/sesamy-post-editor/build/index.js', 
                [ 'wp-plugins', 'wp-edit-post', 'wp-element' ],
                false,
                false
            );
        } );

    }


}