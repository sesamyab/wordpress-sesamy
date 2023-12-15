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
class Sesamy_Passes {

	/**
	 * Class Instance
	 *
	 * @since  1.0.0
	 * @access private
	 * @var array $instance instance of Sesamy_Passes Class
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
			self::$instance = new Sesamy_Passes();
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
			'sesamy_passes',
			$post_types,
			array(
				'label'              => 'Sesamy Passes',
				'singular_label'     => 'Sesamy Pass',
				'query_var'          => true,
				'public'             => false,
				'show_ui'            => true,
				'show_tagcloud'      => false,
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

		register_term_meta(
			'sesamy_passes',
			'price',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'number',
				'default'       => 0,
				'auth_callback' => '__return_true',
			)
		);

		// NOTE: Different form templates are used in add and edit because of WordPress is not streamlined :( .

		add_action( 'sesamy_passes_add_form_fields', array( $this, 'add_taxonomy_form_fields' ) );
		add_action( 'sesamy_passes_edit_form_fields', array( $this, 'edit_taxonomy_form_fields' ) );

		add_action( 'created_sesamy_passes', array( $this, 'save_fields' ) );
		add_action( 'edited_sesamy_passes', array( $this, 'save_fields' ) );

		add_filter( 'pre_insert_term', array( $this, 'prevent_add_term' ), 20, 2 );
	}

	/**
	 * Admin init hook.
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 */
	public function admin_init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
	}

	/**
	 * Admin script enqueue.
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 */
	public function load_scripts() {
		if ( ! isset( $_GET['taxonomy'] ) || 'sesamy_passes' !== $_GET['taxonomy'] ) {
			return;
		}

		// Add wp media support.
		wp_enqueue_media();

		// Load our custom image trigger.
		wp_enqueue_script( 'sesamy-passes-admin', SESAMY_PLUGIN_URL . '/admin/js/sesamy-passes-admin.js', array( 'jquery' ), '1.0', false );
	}

	/**
	 * Check Invalid URL before add new sesamy passes
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param array  $term Term object.
	 * @param string $taxonomy Taxonomy Name.
	 */
	public function prevent_add_term( $term, $taxonomy ) {

		if ( 'sesamy_passes' == $taxonomy  && isset( $_POST['url'] ) && ! empty( $_POST['url'] ) ) {
			if ( wp_http_validate_url( $_POST['url'] ) == FALSE ) {
				$term = new WP_Error( 'invalid_term', 'Please enter valid Public URL.' );
			}
		}
		return $term;
	}

	/**
	 * Save Fields
	 *
	 * @since  1.0.0
	 * @package    Sesamy
	 * @param int $term_id Term ID.
	 */
	public function save_fields( $term_id ) {

		wp_verify_nonce( 'update-tag_' . $term_id );

		update_term_meta( $term_id, 'price', isset( $_POST['price'] ) ? sanitize_text_field( wp_unslash( $_POST['price'] ) ) : '' );
		update_term_meta( $term_id, 'url', isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '' );
		update_term_meta( $term_id, 'image_id', isset( $_POST['image_id'] ) ? sanitize_text_field( wp_unslash( $_POST['image_id'] ) ) : '' );
		update_term_meta( $term_id, 'period', isset( $_POST['period'] ) ? sanitize_text_field( wp_unslash( $_POST['period'] ) ) : '' );
		update_term_meta( $term_id, 'time', isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '' );
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
		<div class="form-field term-group">
			<label for="category-image-id"><?php echo esc_html__( 'Image', 'sesamy' ); ?></label>
			<input type="hidden" id="taxonomy-image-id" name="image_id" class="custom_media_url" value="">
			<div id="category-image-wrapper"></div>
			<p>
			<input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button" name="ct_tax_media_button" value="<?php echo esc_attr__( 'Add Image', 'sesamy' ); ?>" />
			<input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" name="ct_tax_media_remove" value="<?php echo esc_attr__( 'Remove Image', 'sesamy' ); ?>" />
			</p>
		</div>


		<div class="form-field">
			<label for="price"><?php echo esc_html__( 'Price', 'sesamy' ); ?></label>
			<input type="number" step="0.01" min="0.01"  name="price" required />
			<p><?php echo esc_html__( 'Price for this pass', 'sesamy' ); ?></p>
		</div>
		
		<div class="form-field">
			<label for="period"><?php echo esc_html__( 'Payment period', 'sesamy' ); ?></label>
			<?php
			Sesamy_Utils::render_select(
				'period',
				array(
					'monthly' => 'Monthly',
					'yearly'  => 'Yearly',
				)
			);
			?>
			<p><?php echo esc_html__( 'Payment interval for this pass', 'sesamy' ); ?></p>
		</div>

		<div class="form-field">
			<label for="time"><?php echo esc_html__( 'Payment time', 'sesamy' ); ?></label>
			<input type="number" step="1" min="1"  name="time" value="1" required />
			<p><?php echo esc_html__( 'Payment time for this pass. Ex 1 for every month, 3 for every third month.', 'sesamy' ); ?></p>
		</div>
		<div class="form-field">
			<label for="cb_custom_meta_data_url"><?php echo esc_html__( 'Public URL', 'sesamy' ); ?></label>
			<input type="url" name="url" id="cb_custom_meta_data_url" />
			<p><?php echo esc_html__( 'Url where a visitor can read more about the pass. Also used as the item-src identifier with Sesamy.', 'sesamy' ); ?></p>
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

		$price    = get_term_meta( $term->term_id, 'price', true );
		$url      = get_term_meta( $term->term_id, 'url', true );
		$image_id = get_term_meta( $term->term_id, 'image_id', true );
		$period   = get_term_meta( $term->term_id, 'period', true );
		$time     = get_term_meta( $term->term_id, 'time', true );
		?>
		
		<tr class="form-field term-group-wrap">
			<th scope="row">
				<label for="taxonomy-image-id"><?php echo esc_html__( 'Image', 'sesamy' ); ?></label>
			</th>

			<td>
				<input type="hidden" id="taxonomy-image-id" name="image_id" value="<?php echo esc_attr( $image_id ); ?>">
				<div id="category-image-wrapper">
			
					<?php if ( $image_id ) { ?>
						<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
					<?php } ?>

				</div>

				<p>
					<input type="button" class="button button-secondary ct_tax_media_button" id="showcase_tax_media_button" name="showcase_tax_media_button" value="<?php echo esc_attr__( 'Add Image', 'sesamy' ); ?>" />
					<input type="button" class="button button-secondary ct_tax_media_remove" id="showcase_tax_media_remove" name="showcase_tax_media_remove" value="<?php echo esc_attr__( 'Remove Image', 'sesamy' ); ?>" />
				</p>
			</td>
		</tr>

		<tr class="form-field">
		<th><label for="price"><?php echo esc_html__( 'Price', 'sesamy' ); ?></label></th>
		<td><input type="number" step="0.01" min="0.01" name="price" id="price" value="<?php echo esc_attr( $price ); ?>" required />
			<p><?php echo esc_html__( 'Price for this tier', 'sesamy' ); ?></p></td>
		</div>

		<tr class="form-field">
		<th><label for="period"><?php echo esc_html__( 'Payment period', 'sesamy' ); ?></label></th>
		<td>
		<?php
		Sesamy_Utils::render_select(
			'period',
			array(
				'monthly' => 'Monthly',
				'yearly'  => 'Yearly',
			),
			$period
		);
		?>
			<p><?php echo esc_html__( 'Payment period for this pass', 'sesamy' ); ?></p>
		</tr>

		<tr class="form-field">
		<th><label for="time"><?php echo esc_html__( 'Payment time', 'sesamy' ); ?></label></th>
		<td>
			<input type="number" step="1" min="1"  name="time" value="<?php echo esc_attr( $time ); ?>" required />
			<p><?php echo esc_html__( 'Payment time for this pass. Ex 1 for every month, 3 for every third month.', 'sesamy' ); ?></p>
		</tr>

		<th><label for="url"><?php echo esc_html__( 'Public URL', 'sesamy' ); ?></label></th>
		<td><input type="url" name="url" id="url" value="<?php echo esc_url( $url ); ?>" />
			<p><?php echo esc_html__( 'Url where a visitor can read more about the pass. Also used as the item-src identifier with Sesamy.', 'sesamy' ); ?></p></td>
		</div>
		<?php
	}
}
