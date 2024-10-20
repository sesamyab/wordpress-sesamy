# Development

Dependencies for signature validation are implemented with libraries using composer.

Run the following command to install the dependencies:

    cd src && composer update

For debugging gutenberg post editor:

    cd admin/gutenberg/sesamy-post-editor
    npm install
    npm start

(Make sure to hard-refresh to avoid script caching issues)

## Enable codesniffer

Run the following command to enable codesniffer
./vendor/bin/phpcs --config-set default_standard WordPress

## Run the codesniffer

Install VS Code plugin here https://marketplace.visualstudio.com/items?itemName=shevaua.phpcs or run manually:

    ./vendor/bin/phpcs ./src/*  --ignore=*/vendor/*,*.js,*node_modules* --standard=WordPress-Core,WordPress-Extra

You can also run automatic fixes according to standards like this

    ./vendor/bin/phpcbf ./src/*  --ignore=*/vendor/*,*.js,*node_modules* --standard=WordPress-Core,WordPress-Extra

# Plugin configuration

## Setup

## Enable the paywall for an article

## Passes

# Filters and actions

In the main loop the wrapping of sesamy container is hooked into two filters, the_content and sesamy_content.

You can apply the sesamy_content to custom templates, shortcodes and others like this:

    apply_filters( 'sesamy_content', $post, $content );

To modify the data before returned you can add a filter. The default priority for the built in main content is 999.

    add_filter( 'sesamy_content', 'my_callback', 10, 2 );

    function my_callback( $post, $content)

## Custom paywall design

You can customize how paywall will be rendering by supplying your own template in code using the filter `sesamy_paywall` like this:

    add_filter('sesamy_paywall', 'show_paywall', 11, 3);

    function show_paywall( $default_paywall, $post, $post_settings){
        // Code for your custom layout. Please see /demo folder for a complete example
    }

# Commmon issues

## Sesamy section of the the editor is not showing

- Make sure that the javascript packages has been installed and have been built
- Check that the permalinks structure is set to post name: http://localhost:8000/wp-admin/options-permalink.php

# Deploying to Wordpress Store

## Releasing a new version

When releasing a new version, make sure to update the version number in the following files:

- `src/sesamy.php`
- `src/version.php`
- `README.md`

The version number needs to match the tag in github.

## Deploying to Wordpress Store

Run the Release action to push to the Wordpress store. The action will automatically create a zip file and push it to the store.
