<?php

/**
 * Loads the bundle script
 *
 * @link       https://www.sesamy.com
 * @since      1.0.0
 * @author     Sesamy
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

class Sesamy_Bundler {

    /**
     * Load the bundle script
     * It uses a transient to cache the contents of the bundle script
     * We do this to avoid ad-blockers blocking the script
     */
    public function load_bundle() {
        $bundle_script_url = Sesamy::$instance->get_assets_url() . '/scripts/web-components/sesamy-bundle.min.js';

        // Use transient to cache the bundle script url
        $bundle_script_url_transient = get_transient( 'sesamy_bundle_script_url' );

        if ( false === $bundle_script_url_transient ) {

            $bundle_script_url_transient = wp_remote_get( $bundle_script_url );

            if ( ! is_wp_error( $bundle_script_url_transient ) ) {
                $bundle_script_url_transient = $bundle_script_url_transient['body'];
            }

            // Set transient for 5 minutes
            set_transient( 'sesamy_bundle_script_url', $bundle_script_url_transient, 5 * MINUTE_IN_SECONDS );
        }

        ?>
        <script type="module">
            <?php echo $bundle_script_url_transient; ?>
        </script>
        <?php
    }

    /**
     * Action to clear the transient. This is done from the admin settings page.
     */
    public function clear_bundle_transient() {
        delete_transient( 'sesamy_bundle_script_url' );
        
        // Redirect back to the settings page
        $url = add_query_arg( 'settings-updated', 'true', wp_get_referer() );
        wp_safe_redirect( $url );
        exit;
    }

}