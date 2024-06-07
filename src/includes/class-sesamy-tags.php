<?php
/**
 * Sesamy post type and taxnomy
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 */

/**
 * Taxonomy and add form fields.
 *
 * @package    Sesamy
 * @subpackage Sesamy/includes
 * @author     Jonas Stensved <jonas@viggeby.com>
 */
class Sesamy_Tags {

	/**
	 * Class Instance
	 *
	 * @since  1.0.0
	 * @access private
	 * @var array $instance instance of Sesamy_Tags Class
	 */
	private static $instance;

	/**
	 * Get Instance
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @return $instance;
	 */
	public static function get_instance() {
		if ( empty( $instance ) ) {
			self::$instance = new Sesamy_Tags();
		}

		return self::$instance;
	}

	/**
	 * Register taxonomy
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 */
	public function register_taxonomy() {

		$post_types = sesamy_get_enabled_post_types();

		register_taxonomy(
			'sesamy_tags',
			$post_types,
			array(
				'label'              => 'Sesamy Attributes',
				'singular_label'     => 'Sesamy Attribute',
				'query_var'          => true,
				'public'             => false,
				'show_ui'            => true,
				'show_tagcloud'      => true,
				'show_in_nav_menus'  => false,
				'rewrite'            => false,
				'hierarchical'       => true,
				'show_in_rest'       => true,
				'show_in_quick_edit' => false,
				'meta_box_cb'        => false,
				'capabilities'       => array(
					'manage_terms' => 'manage_options',
					'edit_terms'   => 'manage_options',
					'delete_terms' => 'manage_options',
					'assign_terms' => 'edit_posts',
				),
			)
		);

		add_action( 'sesamy_tags_add_form_fields', array( $this, 'add_taxonomy_form_fields' ) );
		add_action( 'sesamy_tags_edit_form_fields', array( $this, 'edit_taxonomy_form_fields' ) );

		add_action( 'created_sesamy_tags', array( $this, 'save_fields' ) );
		add_action( 'edited_sesamy_tags', array( $this, 'save_fields' ) );

		add_filter( 'manage_edit-sesamy_tags_columns', array( $this, 'custom_term_columns' ) );
		add_filter( 'manage_sesamy_tags_custom_column', array( $this, 'custom_term_column_content' ), 10, 3 );
	}

	/**
	 * Add fields on taxonomy page
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $taxonomy Taxonomy name.
	 */
	public function add_taxonomy_form_fields( $taxonomy ) {
		?>
		<div class="form-field">
			<label for="attribute-type"><?php echo esc_html__( 'Attribute Type', 'sesamy' ); ?></label>
			<select name="attribute_type" id="attribute-type">
				<option value="">Select Attribute Type</option>
				<option value="tag">Tag</option>
				<option value="user-metadata">User Metadata</option>
			</select>
			<p><?php echo esc_html__( 'Attribute type for sesamy attribute', 'sesamy' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Edit fields on taxonomy page
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array $term term object.
	 */
	public function edit_taxonomy_form_fields( $term ) {

		$selected_attribute_type = get_term_meta( $term->term_id, 'attribute_type' );
		$selected_attribute_type = is_array( $selected_attribute_type ) ? count( $selected_attribute_type ) > 0 ? $selected_attribute_type[0] : '' : '';
		$attribute_types_array   = array(
			''              => 'Select Attribute Type',
			'tag'           => 'Tag',
			'user-metadata' => 'User Metadata',
		);
		?>
		<tr class="form-field">
			<th>
				<label for="attribute-type"><?php echo esc_html__( 'Attribute Type', 'sesamy' ); ?></label>
			</th>
			<td>
				<select name="attribute_type" id="attribute-type">
					<?php
					foreach ( $attribute_types_array as $key => $value ) :
						?>
					<option value="<?php echo $key; ?>" 
												<?php
												if ( $key == $selected_attribute_type ) {
													?>
						selected <?php } ?>><?php echo $value; ?></option>
					<?php endforeach ?>
				</select>
				<p class="description"><?php echo esc_html__( 'Attribute type for sesamy attribute', 'sesamy' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save Fields
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param int $term_id Term ID.
	 */
	public function save_fields( $term_id ) {
		update_term_meta( $term_id, 'attribute_type', isset( $_POST['attribute_type'] ) ? wp_unslash( $_POST['attribute_type'] ) : '' );
	}

	// Add custom column header
	function custom_term_columns( $columns ) {
		// Define the custom column and its position
		$new_columns = array(
			'custom_column' => 'Attribute Type',
		);
		$columns     = array_slice( $columns, 0, 3, true ) + $new_columns + array_slice( $columns, 3, null, true );
		return $columns;
	}

	// Display custom column content
	function custom_term_column_content( $content, $column_name, $term_id ) {
		if ( 'custom_column' === $column_name ) {
			$get_term_meta = get_term_meta( $term_id, 'attribute_type', true );
			$content       = esc_html__( ucwords( str_replace( '-', ' ', $get_term_meta ) ), 'sesamy' );
		}
		return $content;
	}

}

