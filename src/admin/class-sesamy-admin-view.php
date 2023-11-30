<?php

class Sesamy_Admin_View {

	public function bulk_edit_save( $post_id ) {

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-posts' ) ) {
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

		$passes = get_terms( 'sesamy_passes', array( 'hide_empty' => false ) );
		foreach ( $passes as $pass ) {

			$pass_post_key = 'sesamy_passes_' . esc_attr( $pass->term_id );

			if ( ! empty( $_GET[ $pass_post_key ] ) && in_array( $_GET[ $pass_post_key ], array( 'true', 'false' ), true ) ) {

				$enabled = rest_sanitize_boolean( $_GET[ $pass_post_key ] );

				if ( $enabled ) {
					wp_set_object_terms( $post_id, $pass->term_id, 'sesamy_passes', true );
				} else {
					wp_remove_object_terms( $post_id, $pass->term_id, 'sesamy_passes' );
				}
			}
		}
	}

	public function bulk_edit_fields( $column_name, $post_type ) {

		// WordPress support for creating a nice edit experience here is very limited, hook everything into the sesamy_locked column to keep things together
		if ( 'sesamy_locked' === $column_name ) {

			?>
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
						<span class="title">Pris</span>
						<div>
							<span class="input-text-wrap"><input type="number" step="0.01" min="0.01"  name="sesamy_single_purchase_price" placeholder="" /></span>
							<p class="howto" id="inline-edit-post_tag-desc"><?php echo esc_html__( 'Leave empty to keep price unchanged.', 'sesamy' ); ?></p>
						</div>
					</label>

					<label class="wp-clearfix">
						<span class="title">Valuta</span>                      
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
					$passes = get_terms( 'sesamy_passes', array( 'hide_empty' => false ) );
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
				</div>
				</fieldset>
			<?php
		}
	}

	public function add_featured_columns( $column_array ) {

		$column_array['sesamy_locked']          = __( 'Paywall', 'sesamy' );
		$column_array['sesamy_single_purchase'] = __( 'Single purchase', 'sesamy' );
		$column_array['sesamy_passes']          = __( 'Passes', 'sesamy' );

		return $column_array;
	}

	public function populate_featured_columns( $column_name, $post_id ) {

		switch ( $column_name ) {
			case 'sesamy_locked':
				$post_properties = Sesamy_Post_Properties::get_post_settings( $post_id );
				$sesamy_locked   = Sesamy_Post_Properties::is_locked( $post_id );
				echo esc_html( isset( $sesamy_locked ) && true === boolval( $sesamy_locked ) ? __( 'Locked now', 'sesamy' ) : __( 'Not locked now', 'sesamy' ) );

				if ( ! empty( $post_properties['locked_from'] ) && $post_properties['locked_from'] > 0 ) {
					echo '<p>';
					// Translators: Time when post is locked from
					printf( esc_html__( 'Locked from: %s', 'sesamy' ), esc_html( date_i18n( 'Y-m-d H:i', $post_properties['locked_from'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ) );
					echo '</p>';
				}

				if ( ! empty( $post_properties['locked_until'] ) && $post_properties['locked_until'] > 0 ) {
					echo '<p>';
					// Translators: Time when post is locked until
					printf( esc_html__( 'Locked until: %s', 'sesamy' ), esc_html( date_i18n( 'Y-m-d H:i', $post_properties['locked_until'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ) );
					echo '</p>';
				}

				break;
			case 'sesamy_single_purchase':
				if ( Sesamy_Post_Properties::is_locked( $post_id ) ) {

					$post_info = Sesamy_Post_Properties::get_post_price_info( $post_id );
					if ( true === $post_info['enable_single_purchase'] ) {
						if ( ! empty( $post_info['price'] ) && ! empty( get_option('sesamy_gloabl_currency') ) ) {
							echo esc_html( $post_info['price'] ) . ' ' . esc_html( get_option('sesamy_gloabl_currency') );
						}
					} else {
						echo esc_html( __( 'Disabled', 'sesamy' ) );
					}
				}
				break;
			case 'sesamy_passes':
				if ( Sesamy_Post_Properties::is_locked( $post_id ) ) {
					$passes = Sesamy_Post_Properties::get_post_passes( $post_id );
					if ( is_array( $passes ) ) {

						$passnames = array_map(
							function( $p ) {
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
}
