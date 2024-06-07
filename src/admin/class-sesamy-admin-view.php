<?php
/**
 * Admin view for post page
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/admin
 */

/**
 * Admin view for post page
 *
 * @link  https://www.viggeby.com
 * @since 1.0.0
 *
 * @package    Sesamy
 * @subpackage Sesamy/admin
 */
class Sesamy_Admin_View {
	/**
	 * Bulk Edit Save
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @package    Sesamy
	 */
	public function bulk_edit_save( $post_id ) {

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-posts' ) ) {
			return;
		}

		if ( ! empty( $_GET['sesamy_enable_paywall'] ) && in_array( $_GET['sesamy_enable_paywall'], array( 'true', 'false' ), true ) ) {
			update_post_meta( $post_id, '_sesamy_locked', 'true' === $_GET['sesamy_enable_paywall'] );
		}

		if ( ! empty( $_GET['sesamy_enable_single_purchase'] ) && in_array( $_GET['sesamy_enable_single_purchase'], array( 'true', 'false' ), true ) ) {
			update_post_meta( $post_id, '_sesamy_enable_single_purchase', 'true' === $_GET['sesamy_enable_single_purchase'] );
		}

		if ( ! empty( $_GET['sesamy_single_purchase_price'] ) && is_numeric( $_GET['sesamy_single_purchase_price'] ) ) {
			update_post_meta( $post_id, '_sesamy_price', floatval( $_GET['sesamy_single_purchase_price'] ) );
		}

		// Save Access Level.
		if ( isset( $_GET['access_level'] ) ) {
			$sesamy_access_level = sanitize_text_field( wp_unslash( $_GET['access_level'] ) );
			update_post_meta( $post_id, '_sesamy_access_level', $sesamy_access_level );
		}

		$passes = get_terms(
			array(
				'taxonomy'   => 'sesamy_passes',
				'hide_empty' => false,
			),
		);
		if ( ! empty( $passes ) ) {
			foreach ( $passes as $pass ) {

				$pass_post_key = 'sesamy_passes_' . esc_attr( $pass->term_id );

				if ( ! empty( $_GET[ $pass_post_key ] ) && in_array( $_GET[ $pass_post_key ], array( 'true', 'false' ), true ) ) {

					$enabled = rest_sanitize_boolean( sanitize_text_field( wp_unslash( $_GET[ $pass_post_key ] ) ) );

					if ( $enabled ) {
						wp_set_object_terms( $post_id, $pass->term_id, 'sesamy_passes', true );
					} else {
						wp_remove_object_terms( $post_id, $pass->term_id, 'sesamy_passes' );
					}
				}
			}
		}
	}

	/**
	 * Function to save meta data on save_post action with classic editor
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @package    Sesamy
	 */
	public function sesamy_postmeta_edit_save( $post_id ) {

		// Check if this is an autosave or a revision.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		// if our nonce isn't there, or we can't verify it, bail.
		if ( ! isset( $_POST['post_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['post_meta_box_nonce'] ) ), 'sesamy_post_meta_box_nonce' ) ) {
			return;
		}

		// Check if the current user has permission to edit the post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save or update the custom meta data.
		$locked = ( isset( $_POST['sesamy_enable_locked'] ) ) ? 1 : 0;
		update_post_meta( $post_id, '_sesamy_locked', $locked );

		// Save From date and time.
		if ( isset( $_POST['locked_from_date'] ) ) {
			$fromdate = sanitize_text_field( wp_unslash( $_POST['locked_from_date'] ) );

			// Convert combined datetime string to timestamp.
			$from_timestamp = strtotime( '-6 hours 30 minutes', strtotime( $fromdate ) );
			update_post_meta( $post_id, '_sesamy_locked_from', $from_timestamp );
		} else {
			update_post_meta( $post_id, '_sesamy_locked_from', -1 );
		}

		// Save Untill date and time.
		if ( isset( $_POST['locked_until_date'] ) ) {
			$untilldate = sanitize_text_field( wp_unslash( $_POST['locked_until_date'] ) );

			// Convert combined datetime string to timestamp.
			$untill_timestamp = strtotime( '-6 hours 30 minutes', strtotime( $untilldate ) );
			update_post_meta( $post_id, '_sesamy_locked_until', $untill_timestamp );
		} else {
			update_post_meta( $post_id, '_sesamy_locked_until', -1 );
		}

		// Save single purchase.
		$enable_single_purchase = ( isset( $_POST['sesamy_enable_single_purchase'] ) ) ? 1 : 0;
		update_post_meta( $post_id, '_sesamy_enable_single_purchase', $enable_single_purchase );

		// Save Sesamy passes.
		if ( isset( $_POST['sesamy-post-passes'] ) ) {
			// Set the term for the post.
			$sesamy_post_passes = wp_unslash( $_POST['sesamy-post-passes'] );
			wp_set_object_terms( $post_id, $sesamy_post_passes, 'sesamy_passes' );
		} else {
			wp_set_object_terms( $post_id, array(), 'sesamy_passes' );
		}

		// Save Price.
		if ( isset( $_POST['sesamy_single_purchase_price'] ) ) {
			$sesamy_single_purchase_price = sanitize_text_field( wp_unslash( $_POST['sesamy_single_purchase_price'] ) );
			update_post_meta( $post_id, '_sesamy_price', $sesamy_single_purchase_price );
		}

		// Save Sesamy tags.
		if ( isset( $_POST['sesamy-post-tags'] ) ) {
			// Set the meta for the post.
			$sesamy_tag = wp_unslash( $_POST['sesamy-post-tags'] );
			$sesamy_tag = implode( '|', $sesamy_tag );
			update_post_meta( $post_id, '_sesamy_tags', $sesamy_tag );
			wp_set_post_terms( $post_id, $_POST['sesamy-post-tags'], 'sesamy_tags' );
		} else {
			update_post_meta( $post_id, '_sesamy_tags', '' );
			wp_set_post_terms( $post_id, array(), 'sesamy_tags' );
		}

		// Save Access Level.
		if ( isset( $_POST['access_level'] ) ) {
			$sesamy_access_level = sanitize_text_field( wp_unslash( $_POST['access_level'] ) );
			update_post_meta( $post_id, '_sesamy_access_level', $sesamy_access_level );
		}
	}

	/**
	 * WordPress support for creating a nice edit experience here is very limited, hook everything into the sesamy_locked column to keep things together
	 *
	 * @since 1.0.0
	 * @param string $column_name Column name.
	 * @param string $post_type Post ID.
	 * @package    Sesamy
	 */
	public function bulk_edit_fields( $column_name, $post_type ) {

		if ( 'sesamy_locked' === $column_name ) { ?>
			<fieldset  class="inline-edit-col-left edit-fields-sesamy">
				<span class="inline-edit-legend">Sesamy</span>
				<div class="inline-edit-col">
					
					<label class="wp-clearfix">
						<span class="title"><?php echo esc_html__( 'Enable Paywall', 'sesamy' ); ?></span>
						<?php
						$options = array(
							''      => __( '&mdash; No Change &mdash;' ),
							'true'  => 'Enable',
							'false' => 'Disable',
						);
						Sesamy_Utils::render_select( 'sesamy_enable_paywall', $options );
						?>
					</label>
							

					<label class="wp-clearfix">
						<span class="title"><?php echo esc_html__( 'Enable single purchase', 'sesamy' ); ?></span>
						<?php
						$options = array(
							''      => __( '&mdash; No Change &mdash;' ),
							'true'  => 'Enable',
							'false' => 'Disable',
						);
						Sesamy_Utils::render_select( 'sesamy_enable_single_purchase', $options );
						?>
					</label>

					<label class="wp-clearfix sesamy-bulk-edit-price">
						<span class="title"><?php echo esc_html__( 'Pris', 'sesamy' ); ?></span>
						<div>
							<span class="input-text-wrap"><input type="number" step="0.01" min="0.01"  name="sesamy_single_purchase_price" placeholder="" /></span>
							<p class="howto" id="inline-edit-post_tag-desc"><?php echo esc_html__( 'Leave empty to keep price unchanged.', 'sesamy' ); ?></p>
						</div>
					</label>

					<label class="wp-clearfix">
						<span class="title"><?php echo esc_html__( 'Valuta', 'sesamy' ); ?></span>                      
						<?php
						$options = array_merge(
							array(
								'' => __( '&mdash; No Change &mdash;' ),
							),
							Sesamy_Currencies::get_currencies()
						);

						Sesamy_Utils::render_select( 'sesamy_single_purchase_currency', $options );
						?>
					</label>

		
					<?php
					$passes = get_terms(
						array(
							'taxonomy'   => 'sesamy_passes',
							'hide_empty' => false,
						),
					);
					if ( is_array( $passes ) ) {

						foreach ( $passes as $pass ) {
							?>
								<label class="wp-clearfix">
								<span class="title"><?php echo esc_html( $pass->name ); ?></span>                 
								<?php
								$options = array(
									''      => __( '&mdash; No Change &mdash;' ),
									'true'  => 'Enable',
									'false' => 'Disable',
								);
								Sesamy_Utils::render_select( 'sesamy_passes_' . esc_attr( $pass->term_id ), $options );
								?>
							</label>
							<?php
						}
					}
					?>
						<label class="wp-clearfix sesamy-bulk-edit-price">
							<span class="title"><?php echo esc_html__( 'Access Level', 'sesamy' ); ?></span>
							<select name="access_level" id="access_level">
								<option value="entitlement">Entitlement</option>
								<option value="public">Public</option>
								<option value="logged-in">Small</option>
							</select>
						</label>
				</div>
				</fieldset>
			<?php
		}
	}

	/**
	 * Add Featured column.
	 *
	 * @since 1.0.0
	 * @package Sesamy
	 * @param array $column_array array of columns.
	 * @return $column_array
	 */
	public function add_featured_columns( $column_array ) {

		$column_array['sesamy_locked']          = __( 'Paywall', 'sesamy' );
		$column_array['sesamy_single_purchase'] = __( 'Single purchase', 'sesamy' );
		$column_array['sesamy_passes']          = __( 'Passes', 'sesamy' );

		return $column_array;
	}

	/**
	 * Populate Featured Columns
	 *
	 * @since 1.0.0
	 * @param string $column_name Column name.
	 * @param string $post_id Post ID.
	 * @package    Sesamy
	 */
	public function populate_featured_columns( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'sesamy_locked':
				$post_properties = Sesamy_Post_Properties::get_post_settings( $post_id );
				$sesamy_locked   = Sesamy_Post_Properties::is_locked( $post_id );
				echo esc_html( isset( $sesamy_locked ) && true === boolval( $sesamy_locked ) ? __( 'Locked now', 'sesamy' ) : __( 'Not locked now', 'sesamy' ) );

				if ( ! empty( $post_properties['locked_from'] ) && $post_properties['locked_from'] > 0 ) {
					echo '<p>';
					// Translators: Time when post is locked from.
					printf( esc_html__( 'Locked from: %s', 'sesamy' ), esc_html( date_i18n( 'Y-m-d H:i', $post_properties['locked_from'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ) );
					echo '</p>';
				}

				if ( ! empty( $post_properties['locked_until'] ) && $post_properties['locked_until'] > 0 ) {
					echo '<p>';
					// Translators: Time when post is locked until.
					printf( esc_html__( 'Locked until: %s', 'sesamy' ), esc_html( date_i18n( 'Y-m-d H:i', $post_properties['locked_until'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ) );
					echo '</p>';
				}

				break;
			case 'sesamy_single_purchase':
				if ( Sesamy_Post_Properties::is_locked( $post_id ) ) {

					$post_info = Sesamy_Post_Properties::get_post_price_info( $post_id );
					if ( true === $post_info['enable_single_purchase'] ) {
						if ( ! empty( $post_info['price'] ) && ! empty( get_option( 'sesamy_global_currency' ) ) ) {
							echo esc_html( $post_info['price'] ) . ' ' . esc_html( get_option( 'sesamy_global_currency' ) );
						}
					} else {
						echo esc_html__( 'Disabled', 'sesamy' );
					}
				}
				break;
			case 'sesamy_passes':
				if ( Sesamy_Post_Properties::is_locked( $post_id ) ) {
					$passes = Sesamy_Post_Properties::get_post_passes( $post_id );
					if ( is_array( $passes ) ) {

						$passnames = array_map(
							function ( $p ) {
								return $p->name;
							},
							$passes
						);
						echo esc_html( implode( ', ', $passnames ) );
					}
				}
				break;
		}
	}


	/**
	 * Add meta boxes when classic editor activated
	 * Function to add custom meta box to the post sidebar
	 *
	 * @since 1.0.0
	 * @package    Sesamy
	 */
	public function sesamy_post_sidebar_meta_box() {
		add_meta_box(
			'sesamy_add_custom_meta_box',
			__( 'Sesamy', 'sesamy' ),
			array( $this, 'sesamy_add_class_meta_box' ),
			'post',
			'side',
			'default',
			array(
				'__back_compat_meta_box' => true,
			)
		);
	}


	/**
	 * Callback function to render the content of the meta box
	 *
	 * @param string $post Post Object.
	 * @since 1.0.0
	 * @package    Sesamy
	 */
	public function sesamy_add_class_meta_box( $post ) {

		$post_properties = Sesamy_Post_Properties::get_post_settings( $post->ID );
		$sesamy_locked   = Sesamy_Post_Properties::is_locked( $post->ID );

		$locked_checked = ( 1 == $sesamy_locked ) ? 'checked="checked"' : '';

		// We'll use this nonce field later on when saving.
		wp_nonce_field( 'sesamy_post_meta_box_nonce', 'post_meta_box_nonce' );

		$access_level_args = array( 'entitlement', 'public', 'logged-in' );
		?>

		<fieldset  class="classic-fields-sesamy">
			<input type="checkbox" name="sesamy_enable_locked" <?php echo esc_attr( $locked_checked ); ?> class="sesamy-classic-locked">
			<label class="wp-clearfix sesamy-bulk-edit-price">
				<span class="title"><b><?php echo esc_html( __( 'Locked now', 'sesamy' ) ); ?></b></span>
			</label> 

			<div class="sesamy-classic-locked-active">
				<div class="block-container">
					<?php

						// Add UTC+5:30 (in seconds) to the current timestamp.
						$from_modified_timestamp = strtotime( '+5 hours 30 minutes', $post_properties['locked_from'] );

						$locked_date_from = ( $post_properties['locked_from'] >= 1 ) ? esc_html( date_i18n( 'Y-m-d H:i', $from_modified_timestamp ) ) : '';
					?>

					<label class="wp-clearfix">
						<span class="title"><?php echo esc_html( __( 'LOCKED FROM', 'sesamy' ) ); ?></span>
					</label>
					<input type="datetime-local" name="locked_from_date" value="<?php echo esc_attr( $locked_date_from ); ?>">
				</div>
			</div>

			<div class="sesamy-classic-locked-inactive">

				<?php

					// Add UTC+5:30 (in seconds) to the current timestamp.
					$untill_modified_timestamp = strtotime( '+5 hours 30 minutes', $post_properties['locked_until'] );

					$locked_date_until = ( $post_properties['locked_until'] >= 1 ) ? esc_html( date_i18n( 'Y-m-d H:i', $untill_modified_timestamp ) ) : '';
				?>
				<div class="block-container">
					<label class="wp-clearfix sesamy-bulk-edit-price">
						<span class="title"><?php echo esc_html( __( 'LOCKED UNTIL', 'sesamy' ) ); ?></span>
					</label>
					<input type="datetime-local" name="locked_until_date" value="<?php echo esc_attr( $locked_date_until ); ?>">
				</div>

				<div class="block-container">
					<?php
					$enable_single_purchase = ( 1 == $post_properties['enable_single_purchase'] ) ? 'checked="checked"' : '';
					?>
					<label class="wp-clearfix sesamy-bulk-edit-price">
						<span class="title"><?php echo esc_html( __( 'SINGLE PURCHASE', 'sesamy' ) ); ?></span>
					</label>

					<div class="sesamy-classic-purchase-container">
						<input type="checkbox" name="sesamy_enable_single_purchase" <?php echo esc_attr( $enable_single_purchase ); ?> class="sesamy-classic-single-purchase"><?php echo esc_html( __( 'Enable Single Purchase', 'sesamy' ) ); ?>
					</div>
				</div>

				<div class="sesamy-classic-locked-price block-container">
					<label class="wp-clearfix sesamy-bulk-edit-price">
						<span class="title"><?php echo esc_html( __( 'Price', 'sesamy' ) ); ?></span>
					</label>
											
					<input type="number" name="sesamy_single_purchase_price" value="<?php echo esc_attr( $post_properties['price'] ); ?>">
				</div>

				<div class="block-container">
					<label class="wp-clearfix sesamy-bulk-edit-price">
						<span class="title"><?php echo esc_html( __( 'SESAMY PASSES', 'sesamy' ) ); ?></span>
					</label>

					<?php
					$passes = Sesamy_Post_Properties::get_post_passes( $post->ID );
					$args   = array(
						'taxonomy'   => 'sesamy_passes',
						'hide_empty' => false,
					);

					// Get sesamy_passes categories.
					$categories = get_terms( $args );

					// Output category checkboxes.
					if ( ! empty( $categories ) ) {
						foreach ( $categories as $category ) {
							$checked = in_array( $category->term_id, wp_list_pluck( $passes, 'term_id' ) ) ? 'checked="checked"' : '';
							echo '<input type="checkbox" name="sesamy-post-passes[]"  value="' . esc_attr( $category->name ) . '" ' . $checked . '>';
							echo esc_html( $category->name );
							echo '<br>';
						}
					}
					?>
				</div>	    

				<div class="block-container">
					<label class="wp-clearfix sesamy-bulk-edit-price">
						<span class="title"><?php echo esc_html( __( 'Sesamy Attributes', 'sesamy' ) ); ?></span>
					</label>
					
					<?php
					$args = array(
						'taxonomy'   => 'sesamy_tags',
						'hide_empty' => false,
					);

					// Get sesamy_tags categories.
					$sesamy_tags          = get_terms( $args );
					$selected_sesamy_tags = $post_properties['sesamy_tags'] ? is_array( $post_properties['sesamy_tags'] ) ? $post_properties['sesamy_tags'] : explode( '|', $post_properties['sesamy_tags'] ) : array();

					// Output tag checkboxes.
					if ( ! empty( $sesamy_tags ) ) {
						foreach ( $sesamy_tags as $sesamy_tag ) {
							$checked = in_array( $sesamy_tag->term_id, $selected_sesamy_tags ) ? 'checked="checked"' : '';
							echo '<input type="checkbox" name="sesamy-post-tags[]"  value="' . esc_attr( $sesamy_tag->term_id ) . '" ' . $checked . '>';
							echo esc_html( $sesamy_tag->name );
							echo '<br>';
						}
					}
					?>
				</div>
				
				<div class="block-container">
					<label class="wp-clearfix sesamy-bulk-edit-price">
						<span class="title"><?php echo esc_html__( 'Access Level', 'sesamy' ); ?></span>
					</label>
					<div>
						<?php $access_level = ( isset( $post_properties['access_level'] ) && $post_properties['access_level'] != -1 ) ? $post_properties['access_level'] : 'entitlement'; ?>
						<select name="access_level" id="access_level">
							<?php
							if ( ! empty( $access_level_args ) ) {
								foreach ( $access_level_args as $key => $level_text ) {
									?>
									<option value="<?php echo esc_attr( $level_text ); ?>" <?php echo ( $access_level === $level_text ) ? 'selected=selected' : ''; ?>> 
										<?php echo esc_html__( ucfirst( $level_text ), 'sesamy' ); ?>  
									</option>
									<?php
								}
							}
							?>
						</select>
					</div>
				</div>
			</div>
		</fieldset>
		<?php
	}
}
