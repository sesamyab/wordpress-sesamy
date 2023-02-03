=== Sesamy ===
Contributors: jstensved
Tags: sesamy, paywall
Requires at least: 5.0.1
Requires PHP: 5.6
Tested up to: 6.1
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add paywall functionality with Sesamy (sesamy.com) to your WordPress website.

== Description ==

The Sesamy plugin adds paywall functionality for posts and custom post types using Sesamy to your WordPress website without having to write code to integrate yourself.

Features:

* Configure which post types plugin should enable the paywall
* Ability to turn the paywall on/off per post
* Access levels to posts based on both single-purchase and Sesamy passes.
* Set a custom price and/or pass per article
* Ability to configure protection level based on how secure your content is


Please note that using this plugin requires an account with Sesamy (sesamy.com).


== Installation ==

This section describes how to install the plugin;

1. First log in to your website admin panel.
1. Then go to Plugins page Add New > Upload Plugin.
1. Click “Choose file” then select our plugin .zip file.
1. Install and activate the plugin.
1. Add your client_id from the "Sesamy" menu option with a account that has at least `manage_options` permissions and configure your preferences

Requirements:

* You must have pretty permalinks active for the plugin to work

== Frequently Asked Questions ==

= How do I edit passes? =

Under each post type with the paywall functionality enabled users with `manage_options` capability can add, edit and remove passes.

= Are there any filters or actions to modify functioanlity? =

In the main loop the wrapping of sesamy container is hooked into two filters, `the_content` and `sesamy_content`.

You can apply the sesamy_content to custom templates, shortcodes and others like this:

    apply_filters( 'sesamy_content', $post, $content );

To modify the data before returned you can add a filter. The default priority for the built in main content is 999.

    add_filter( 'sesamy_content', 'my_callback', 10, 2 );

    function my_callback( $post, $content) {
        // Filter content here
    }

= How do I customize the paywall design? =

You can customize how the paywall will be rendered by supplying your own template in code using the filter `sesamy_paywall` like this:

        add_filter('sesamy_paywall', 'show_paywall', 11, 3);
        function show_paywall( $default_paywall, $post, $post_settings){
            // Code for your custom layout. Please see link below for a complete example on how to create a custom design
        }`

For a more complete example, please see the folder "demo" in our source code repository at https://github.com/sesamyab/wordpress-sesamy


== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 1.0.3 =
* Sanitized output for security

= 1.0.2 =
* Adjustments and bugfixes for security

= 1.0.1 =
* Adjustments and bugfixes

= 1.0.0 =
* This is the first release of the plugin.
