<?php

// First we need to load the composer autoloader, so we can use WP Mock
require_once dirname( __DIR__, 1 ) . '/vendor/autoload.php';

// Bootstrap WP_Mock to initialize built-in features
WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();


function plugin_dir_path( $path ) {
	return dirname( __DIR__, 1 ) . '/src/';
}

/**
 * Now we include any plugin files that we need to be able to run the tests. This
 * should be files that define the functions and classes you're going to test.
 */
require '/wordpress/wp-load.php';
require dirname( __DIR__, 1 ) . '/src/vendor/autoload.php';
require dirname( __DIR__, 1 ) . '/src/includes/class-sesamy-signed-url.php';




