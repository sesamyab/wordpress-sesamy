=== Sesamy ===
Contributors: markussesamy
Tags: sesamy, paywall
Requires at least: 5.0.1
Requires PHP: 7.4
Tested up to: 6.7.1
Stable tag: 3.0.11
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
* Ability to bulk edit paywall settings when bulk editing posts in WordPress Admin


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

== Filters ==

The following filters can be used to modify the default output from the plugin:

    add_filter( 'sesamy_content', 'my_sesamy_content', 10, 2);
    function my_sesamy_content( $post, $content ) {
        return $content;
    }

    add_filter( 'sesamy_content_container', 'my_sesamy_content_container', 10, 1);
    function ( $content_container ){
        return $content_container;
    }

    add_filter( 'sesamy_paywall_seo', 'my_paywall_seo_callback', 10, 2);
    function my_paywall_seo_callback( $default_seo, $post ) {
        return $default_seo;
    }

    // The preview in the paywall
    add_filter( 'sesamy_paywall_preview', 'my_sesamy_paywall_preview', 10, 1);
    function my_sesamy_paywall_preview( $default_preview ) {
        return $default_preview;
    }


== Changelog ==

= 3.0.11 =
* Always hide the paywall if the user has access to the content.

= 3.0.10 =
* Downgrade wordpress/scripts to support older WP versions.

= 3.0.8 =
* Remove not needed react dependencies

= 3.0.5 =
* Remove in_the_loop check when applying content filter.

= 3.0.0 =
* Use the paywall wizard config from the portal.

= 2.3.2 =
* Fix json encode function.

= 2.3.1 =
* Sanitize description of the paywall wizard.
* Add border to the paywall wizard.

= 2.3.0 =
* Default paywall wizard values (override post values switch).

= 2.2.2 =
* Enable/disable paywall wizard by default in the global settings.
* GBP currency support.
* Fix the single purchase description in the paywall wizard.
* Fix the paywall wizard width in the second stpe.

= 2.1.1 =
* Show login button default to true.

= 2.1.0 =
* Enable/disable paywall wizard in the edit post screen.
* Show/Hide login button in the edit post screen.

= 2.0.7 =
* Fix to always display the content for "None" lock mode

= 2.0.6 =
* Fix to display the content if "Locked Content" is false for "Event" and "Signed URL" lock modes

= 2.0.5 =
* Fix a duplicated preview in unlocked content with "Embed" lock mode

= 2.0.4 =
* Fix to display the content in "Public" access level for "Event" and "Signed URL" lock modes
* Fix to hide the paywall if the content is public

= 2.0.1 =
* Fixed show content if signed url not locked

= 2.0 =
* Removed unnecessary scripts and tags

= 1.0.9 =
* Fixed Sesamy Attribute count issue

= 1.0.8 =
* Added sesamy attribute support

= 1.0.7 =
* Fixed API endpoint issue
* Fixed wp_kses_post issue

= 1.0.6 =
* Added classic editor support 
* Added Access level option in post
* Added additional article meta data
* Removed currency from post and added global currency in general settings

= 1.0.5 =
* Added admin columns for easy overview of paywall settings
* Added support for bulk editing paywall settings through WordPress bulk edit
* Moved settings for endpoint from settings to check if constant SESAMY_DEV_API is set and true

= 1.0.4 =
* Adjusted code formatting and added output escaping

= 1.0.3 =
* Sanitized output for security

= 1.0.2 =
* Adjustments and bugfixes for security

= 1.0.1 =
* Adjustments and bugfixes

= 1.0.0 =
* This is the first release of the plugin.
