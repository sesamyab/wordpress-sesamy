<?php
/**
 * Sesamy Make a tag
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * Sesamy Make a tag
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Utils {
	/**
	 * Remove empty values and convert array into html attributes
	 *
	 * @param array $atts JWT Token.
	 * @since  1.0.0
	 * @package    Sesamy
	 */
	public static function html_attributes( array $atts ) {

		$non_empty_atts = self::remove_empty_values( $atts );

		foreach ( $non_empty_atts as $key => $value ) {
			echo esc_attr( str_replace( '_', '-', $key ) ) . '="' . esc_attr( $value ) . '" ';
		}
	}

	/**
	 * Remove empty values to avoid creating empty attributes
	 *
	 * @param array $atts JWT Token.
	 * @since  1.0.0
	 * @package    Sesamy
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
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param str     $name tag name.
	 * @param array   $atts tag attributes.
	 * @param str     $content tag content.
	 * @param boolean $self_close True false.
	 */
	public static function get_tag( $name, $atts, $content, $self_close = true ) {

		ob_start();
		self::make_tag( $name, $atts, $content, $self_close );
		return ob_get_clean();
	}

	/**
	 * Generate tag, empty content will generate a self-closing tag unless $self_close is false
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param str     $name tag name.
	 * @param array   $atts tag attributes.
	 * @param str     $content tag content.
	 * @param boolean $self_close True false.
	 * @param boolean $return True false.
	 */
	public static function make_tag( $name, $atts, $content, $self_close = true, $return = false ) {

		echo '<' . esc_attr( $name ) . ( count( $atts ) > 0 ? ' ' : '' );

		self::html_attributes( $atts );

		echo '>';

		echo ( empty( $content ) && $self_close ) ? '/>' : wp_kses_post( $content ) . '</' . esc_attr( $name ) . '>';
	}

	/**
	 * Select dropdown Render
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param str   $name tag name.
	 * @param array $options options array.
	 * @param str   $field_value option value.
	 */
	public static function render_select( $name, $options, $field_value = null ) {

		?>
		<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>">
		<?php
		foreach ( $options as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '"' . ( $field_value === $key ? 'selected' : '' ) . '>' . esc_html__( $value, 'sesamy' ) . '</option>';
		}
		?>
		</select>
		<?php
	}
}
