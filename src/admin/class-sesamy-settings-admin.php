<?php

class Sesamy_Settings_Admin {



	public function admin_menu() {

		add_menu_page(
			'Sesamy',
			'Sesamy',
			'manage_options',
			'sesamy',
			array( $this, 'admin_menu_html' )
		);
	}


	public function admin_menu_html() {
		include plugin_dir_path( __DIR__ ) . 'admin/partials/sesamy-admin-display.php';
	}


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
					'signedUrl' => 'Signed URL',
					'event'     => 'Event',
					'none'      => 'None',
				),
				'label_for' => 'sesamy_content_types',
			)
		);
	}


	public function sesamy_section_general_callback() {
		// Add if needed
	}

	public function settings_render_select( $args ) {

		$settings_value = get_option( $args['name'] );

		echo '<select name="' . esc_attr( $args['name'] ) . '">';
		foreach ( $args['options'] as $key => $value ) {
			$selected = $key === $settings_value ? 'selected' : '';
			echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $selected ) . '>' . esc_html( $value ) . '</option>';
		}
		echo '</select>';
	}


	public function settings_render_checkboxlist( $args ) {

		$options = get_option( $args['name'] );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		foreach ( $args['options'] as $key => $value ) {
			$checked = in_array( $key, $options, true ) ? 'checked' : '';
			echo '<label><input type="checkbox" name="' . esc_attr( $args['name'] ) . '[]" value="' . esc_attr( $key ) . '" ' . esc_attr( $checked ) . '>' . esc_attr( $value ) . '</label><br>';
		}
	}

	/**
	 *
	 */
	public function settings_render_input( $args ) {

		$settings_value = get_option( $args['name'] );

		echo '<input type="text" name="' . esc_attr( $args['name'] ) . '" value="' . esc_attr( $settings_value ) . '">';
	}


}

