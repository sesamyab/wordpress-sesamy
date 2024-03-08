<?php
/**
 * The admin settings functionality of the plugin.
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/admin
 */

/**
 * The admin settings functionality of the plugin.
 *
 * Admin settings fields and admin menu
 *
 * @package    Sesamy
 * @subpackage Sesamy/admin
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Settings_Admin {
	/**
	 * Admin menu Sesamy
	 *
	 * @since 1.0.0
	 * @package    Sesamy
	 */
	public function admin_menu() {

		add_menu_page(
			'Sesamy',
			'Sesamy',
			'manage_options',
			'sesamy',
			array( $this, 'admin_menu_html' )
		);
	}

	/**
	 * Menu Html file include.
	 *
	 * @since 1.0.0
	 * @package    Sesamy
	 */
	public function admin_menu_html() {
		include plugin_dir_path( __DIR__ ) . 'admin/partials/sesamy-admin-display.php';
	}

	/**
	 * Admin settings
	 *
	 * @since 1.0.0
	 * @package    Sesamy
	 */
	public function add_settings() {

		add_settings_section(
			'sesamy_section_general',
			__( 'General', 'sesamy' ),
			array( $this, 'sesamy_section_general_callback' ),
			'sesamy'
		);

		add_settings_field(
			'sesamy_client_id',
			__( 'Client ID', 'sesamy' ),
			array( $this, 'settings_render_input' ),
			'sesamy',
			'sesamy_section_general',
			array(
				'name'      => 'sesamy_client_id',
				'label_for' => 'sesamy_client_id',
			)
		);

		add_settings_field(
			'sesamy_content_types',
			__( 'Content types', 'sesamy' ),
			array( $this, 'settings_render_checkboxlist' ),
			'sesamy',
			'sesamy_section_general',
			array(
				'name'      => 'sesamy_content_types',
				'options'   => get_post_types( array( 'public' => true ) ),
				'label_for' => 'sesamy_content_types',
			)
		);

		add_settings_field(
			'sesamy_lock_mode',
			__( 'Lock mode', 'sesamy' ),
			array( $this, 'settings_render_select' ),
			'sesamy',
			'sesamy_section_general',
			array(
				'name'      => 'sesamy_lock_mode',
				'options'   => array(
					'embed'     => 'Embed',
					'encode'    => 'Encode',
					'signedUrl' => 'Signed URL',
					'event'     => 'Event',
					'none'      => 'None',
				),
				'label_for' => 'sesamy_content_types',
			)
		);

		add_settings_field(
			'sesamy_gloabl_currency',
			esc_html__( 'Currency', 'sesamy' ),
			array( $this, 'settings_render_select' ),
			'sesamy',
			'sesamy_section_general',
			array(
				'name'      => 'sesamy_gloabl_currency',
				'options'   => Sesamy_Currencies::get_currencies(),
				'label_for' => 'sesamy_content_types',
			)
		);

		$sesamy_tags = get_terms([
			'taxonomy' => "sesamy_tags",
			'hide_empty' => false,
		]);
		
		$sesamy_tags_array = array();
		if(count($sesamy_tags) > 0) {
			foreach($sesamy_tags as $sesamy_tag) {
				$sesamy_tags_array[$sesamy_tag->term_id] = $sesamy_tag->name;
			}
		}
		
		add_settings_field(
			'sesamy_tags',
			__( 'Sesamy Attributes', 'sesamy' ),
			array( $this, 'settings_render_tag_checkboxlist' ),
			'sesamy',
			'sesamy_section_general',
			array(
				'name'      => 'sesamy_tags',
				'options'   => $sesamy_tags_array,
				'label_for' => 'sesamy_content_types',
			)
		);
	}

	/**
	 * General Section callback.
	 *
	 * @since 1.0.0
	 * @package    Sesamy
	 */
	public function sesamy_section_general_callback() {
		// Add if needed.
	}

	/**
	 * Select field render
	 *
	 * @param array $args Arguments of select fields.
	 * @since 1.0.0
	 * @package    Sesamy
	 */
	public function settings_render_select( $args ) {

		if ( ! empty( $args ) && isset( $args['name'] ) ) {
			$settings_value = get_option( $args['name'] );
			echo '<select name="' . esc_attr( $args['name'] ) . '">';
			foreach ( $args['options'] as $key => $value ) {
				$selected = $key === $settings_value ? 'selected' : '';
				echo '<option value="' . esc_attr( $key ) . '"' .  $selected  . '>' . esc_html( $value ) . '</option>';
			}
			echo '</select>';
		}
	}

	/**
	 * Checkbox field render
	 *
	 * @param array $args Arguments of checkbox fields.
	 * @since 1.0.0
	 * @package    Sesamy
	 */
	public function settings_render_checkboxlist( $args ) {

		if ( ! empty( $args ) ) {
			$options = get_option( $args['name'] );

			if ( ! is_array( $options ) ) {
				$options = array();
			}

			foreach ( $args['options'] as $key => $value ) {
				$checked = in_array( $key, $options, true ) ? 'checked' : '';
				echo '<label><input type="checkbox" name="' . esc_attr( $args['name'] ) . '[]" value="' . esc_attr( $key ) . '" ' . esc_attr( $checked ) . '>' . esc_attr( $value ) . '</label><br>';
			}
		}
	}

	/**
	 * Checkbox field render
	 *
	 * @param array $args Arguments of checkbox fields.
	 * @since 1.0.0
	 * @package    Sesamy
	 */
	public function settings_render_tag_checkboxlist( $args ) {
		if ( ! empty( $args ) ) {
			$options = get_option( $args['name'] );
			if ( ! is_array( $options ) ) {
				$options = array();
			}
			
			foreach ( $args['options'] as $key => $value ) {
				$checked = in_array( $key, $options ) ? 'checked' : '';
				echo '<label><input type="checkbox" name="' . esc_attr( $args['name'] ) . '[]" value="' . esc_attr( $key ) . '" ' . esc_attr( $checked ) . '>' . esc_attr( $value ) . '</label><br>';
			}
		}
	}
	
	/**
	 * Text field render
	 *
	 * @param array $args Arguments of text fields.
	 * @since 1.0.0
	 * @package    Sesamy
	 */
	public function settings_render_input( $args ) {
		if ( ! empty( $args ) ) {
			$settings_value = get_option( $args['name'] );

			echo '<input type="text" name="' . esc_attr( $args['name'] ) . '" value="' . esc_attr( $settings_value ) . '">';
		}
	}
}
