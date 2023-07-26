<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/admin/partials
 */

// check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

// add error/update messages

// check if the user have submitted the settings
// WordPress will add the "settings-updated" $_GET parameter to the url
if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore
	// add settings saved message with the class of "updated"
	add_settings_error( 'sesamy_messages', 'sesamy_message', __( 'Settings Saved', 'sesamy' ), 'updated' );
}

// show error/update messages
settings_errors( 'sesamy_messages' );
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="options.php" method="post">
		<?php
		// output security fields for the registered setting "sesamy"
		settings_fields( 'sesamy' );
		// output setting sections and their fields
		// (sections are registered for "sesamy", each field is registered to a specific section)
		do_settings_sections( 'sesamy' );
		// output save settings button
		submit_button( 'Save Settings' );
		?>
	</form>

	<h2>Clear transients</h2>
	<p>Clear the transient that caches the bundle script.</p>
	<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
		<input type="hidden" name="action" value="sesamy_clear_bundle_transient">
		<?php
		submit_button( 'Clear transients' );
		?>
	</form>

</div>
<?php
