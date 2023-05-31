<?php


class Sesamy_Utils {

	/**
	 * Remove empty values and convert array into html attributes
	 */
	public static function html_attributes( array $atts ) {

		$non_empty_atts = self::remove_empty_values( $atts );

		foreach ( $non_empty_atts as $key => $value ) {
			echo esc_attr( str_replace( '_', '-', $key ) ) . '="' . esc_attr( $value ) . '" ';
		}
	}

	/*
	* Remove empty values to avoid creating empty attributes
	*/
	public static function remove_empty_values( array $atts ) {
		return array_filter(
			$atts,
			function ( $value ) {
				return ! empty( $value );
			}
		);
	}

	/**
	 * Same ase make_tag but return the content
	 */
	public static function get_tag( $name, $atts, $content, $self_close = true ) {

		ob_start();
		self::make_tag( $name, $atts, $content, $self_close );
		return ob_get_clean();
	}

	/**
	 * Generate tag, empty content will generate a self-closing tag unless $self_close is false
	 */
	public static function make_tag( $name, $atts, $content, $self_close = true, $return = false ) {

		echo '<' . esc_attr( $name ) . ( count( $atts ) > 0 ? ' ' : '' );

		self::html_attributes( $atts );

		echo '>';

		echo ( empty( $content ) && $self_close ) ? '/>' : wp_kses_post( $content ) . '</' . esc_attr( $name ) . '>';
	}

	public static function render_select( $name, $options, $field_value = null ) {

		?>
		<select name="<?php echo esc_html( $name ); ?>" id="<?php echo esc_html( $name ); ?>">
		<?php
		foreach ( $options as $key => $value ) {
			echo '<option value="' . esc_html( $key ) . '"' . ( $field_value === $key ? 'selected' : '' ) . '>' . esc_html( $value ) . '</option>';
		}
		?>
		</select>
		<?php
	}

}
