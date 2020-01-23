<?php


$schema = new RP_Schema_Model( array( 'CreativeWork' => array( 'Movie', 'Book', 'Course', 'Diet', 'Game', 'Painting' ), 'Thing' => array( 'Product' ) ) );

$review = $model->review;
if ( empty( $review ) ) {
	return;
}
$check = $review->is_active() ? 'yes' : 'no';
?>
<p class="rp-active rp-<?php echo $check; ?>">
	<label for="rp-review-yes"><?php _e( 'Is this a review post ?', 'wp-product-review' ); ?> </label>
	<?php
	echo $html_helper->radio(
		array(
			'name'    => 'rp-review-status',
			'id'      => 'rp-review-yes',
			'class'   => 'rp-review-status',
			'value'   => 'yes',
			'current' => $check,
		)
	);
	?>
	<label for="rp-review-no"><?php _e( 'Yes', 'wp-product-review' ); ?></label>
	<?php
	echo $html_helper->radio(
		array(
			'name'    => 'rp-review-status',
			'id'      => 'rp-review-no',
			'class'   => 'rp-review-status',
			'value'   => 'no',
			'current' => $check,
		)
	);
	?>
	<label for="rp-review-no"><?php _e( 'No', 'wp-product-review' ); ?></label>

</p>
<div class="rp-review-editor " id="rp-meta-<?php echo $check; ?>">

	<?php do_action( 'rp_editor_before', $model->post ); ?>
	<div class="rp-review-details rp-review-section">
		<h4><?php _e( 'Product Details', 'wp-product-review' ); ?></h4>
		<p><?php _e( 'Specify the general details for the reviewed product.', 'wp-product-review' ); ?></p>
		<?php do_action( 'rp_editor_details_before', $model->post ); ?>
		<div class="rp-review-details-fields rp-review-fieldset">
			<ul>
				<div class="rp-review-type">
					<h5>
						<label for="rp-editor-review-type"><?php _e( 'Review Type', 'wp-product-review' ); ?></label>
						<?php
						$schema_types = $schema->get_types();
						$default = $review->get_type();
						if ( empty( $default ) ) {
							$default = 'Product';
						}
						echo $html_helper->select(
							array(
								'name'      => 'rp-editor-review-type',
								'id'        => 'rp-editor-review-type',
								'value'     => $default,
								'options'   => array_combine(
									array_keys( $schema_types ),
									array_keys( $schema_types )
								),
							)
						);
						?>
					</h5>
					<div class="rp-review-type-fields"></div>
				</div>

				<?php
				$templates = apply_filters( 'rp_review_templates', array( 'default', 'style1', 'style2' ) );
				if ( $templates ) {
					?>
					<li>
						<label for="rp-editor-template"><?php _e( 'Template', 'wp-product-review' ); ?></label>
						<?php
						foreach ( $templates as $template ) {
							$template_id = 'rp-review-template-' . esc_attr( $template );
							echo $html_helper->radio(
								array(
									'name'    => 'rp-review-template',
									'id'      => $template_id,
									'class'   => 'rp-review-template',
									'value'   => $template,
									'current' => $review->get_template(),
									'options' => array(
										'disabled' => ! defined( 'RP_PRO_SLUG' ) && 'default' !== $template,
									),
								)
							);
							?>
							<label for="<?php echo $template_id; ?>">
							<?php
								$image  = null;
							if ( file_exists( RP_PATH . "/assets/img/templates/$template.png" ) ) {
								$image  = RP_URL . "/assets/img/templates/$template.png";
							} elseif ( file_exists( get_stylesheet_directory() . "/rp/$template.png" ) ) {
								$image  = get_stylesheet_directory_uri() . "/rp/$template.png";
							}
							if ( $image ) {
								?>
							<img src='<?php echo $image; ?>' class="rp-review-template"/>
								<?php
							}
							?>
							</label>
							<?php
						}
						?>
					</li>
					<?php
				}
				if ( ! defined( 'RP_PRO_SLUG' ) ) {
					?>
					<label class="rp-upsell-label"><?php echo sprintf( esc_html__( 'You will need the %1$spremium%2$s version to use the extra review templates. You can checkout this %3$sdemo%4$s to see how they are looking.', 'wp-product-review' ), '<a href="' . RP_UPSELL_LINK . '">', '</a>', '<a href="https://demo.themeisle.com/wp-product-review/multiple-review-templates/">', '</a>' ); ?></label>
					<br/>
					<?php
				}
				?>
				<li>
					<label for="rp-editor-product-name"><?php _e( 'Product Name', 'wp-product-review' ); ?></label>
					<?php
					echo $html_helper->text(
						array(
							'name'        => 'rp-editor-product-name',
							'value'       => $review->get_name(),
							'placeholder' => __( 'Product name', 'wp-product-review' ),
						)
					);
					?>
				</li>
				<li>
					<label for="rp-editor-product-image"><?php _e( 'Product Image', 'wp-product-review' ); ?></label>
					<?php
					echo $html_helper->image(
						array(
							'name'   => 'rp-editor-image',
							'value'  => $review->get_image(),
							'action' => __( 'Choose or Upload an Image', 'wp-product-review' ),
						)
					)
					?>
					<small class="size-text">
						*<?php _e( 'The optimum size of the product image must be 600 x 600 px', 'wp-product-review' ); ?>
					</small>
					<small>
						*<?php _e( 'If no image is provided, featured image is used', 'wp-product-review' ); ?>
					</small>
				</li>
				<li>
							<span><?php _e( 'Product Image Click', 'wp-product-review' ); ?>
								: </span>
					<?php
					echo $html_helper->radio(
						array(
							'name'    => 'rp-editor-link',
							'id'      => 'rp-editor-link-show',
							'class'   => 'rp-editor-link',
							'value'   => 'image',
							'current' => $model->get_value( 'rp-editor-link' ),
						)
					);
					?>
					<label for="rp-editor-link-show"><?php _e( 'Show Whole Image', 'wp-product-review' ); ?></label>
					<?php
					echo $html_helper->radio(
						array(
							'name'    => 'rp-editor-link',
							'id'      => 'rp-editor-link-open',
							'class'   => 'rp-editor-link',
							'value'   => 'link',
							'current' => $model->get_value( 'rp-editor-link' ),
						)
					);
					?>
					<label for="rp-editor-link-open"><?php _e( 'Open Affiliate link', 'wp-product-review' ); ?> </label>

				</li>
				<?php
				$links = $review->get_links();
				?>
				<li>
					<label for="rp-editor-button-text"><?php _e( 'Affiliate Button Text', 'wp-product-review' ); ?> </label>
					<?php
					echo $html_helper->text(
						array(
							'name'        => 'rp-editor-button-text',
							'value'       => $model->get_value( 'rp-editor-button-text' ),
							'placeholder' => __( 'Affiliate Button Text', 'wp-product-review' ),
						)
					);
					?>
				</li>
				<li>
					<label for="rp-editor-button-link"><?php _e( 'Affiliate Link', 'wp-product-review' ); ?> </label>
					<?php
					echo $html_helper->text(
						array(
							'name'        => 'rp-editor-button-link',
							'value'       => $model->get_value( 'rp-editor-button-link' ),
							'placeholder' => __( 'Affiliate Link', 'wp-product-review' ),
						)
					);
					?>
					<?php if ( count( $links ) < 2 ) : ?>
						<a id="rp-editor-new-link"
						   title="<?php _e( 'Add new link', 'wp-product-review' ); ?>">+
						</a>
					<?php endif; ?>
				</li>
				<?php if ( count( $links ) < 2 ) { ?>
					<li class="hidden_fields" style="display: none;">
						<label for="rp-editor-button-text"><?php _e( 'Affiliate Button Text', 'wp-product-review' ); ?> </label>
						<?php
						echo $html_helper->text(
							array(
								'name'        => 'rp-editor-button-text-2',
								'value'       => $model->get_value( 'rp-editor-button-text-2' ),
								'placeholder' => __( 'Affiliate Button Text', 'wp-product-review' ),
							)
						);
						?>
					</li>
					<li class="hidden_fields" style="display: none;">
						<label for="rp-editor-button-link"><?php _e( 'Affiliate Link', 'wp-product-review' ); ?> </label>
						<?php
						echo $html_helper->text(
							array(
								'name'        => 'rp-editor-button-link-2',
								'value'       => $model->get_value( 'rp-editor-button-link-2' ),
								'placeholder' => __( 'Affiliate Link', 'wp-product-review' ),
							)
						);
						?>
					</li>
					<?php
				}
				?>
				<?php
				if ( ! empty( $links ) ) {
					if ( count( $links ) > 1 ) {
						$i = 1;
						foreach ( $links as $text => $url ) {
							if ( $i > 1 ) {
								?>
								<li>
									<label for="rp-editor-button-text-<?php $i; ?>"><?php echo __( 'Affiliate Button Text', 'wp-product-review' ) . ' ' . $i; ?> </label>
									<?php
									echo $html_helper->text(
										array(
											'name'        => 'rp-editor-button-text-' . $i,
											'value'       => $text,
											'placeholder' => __( 'Affiliate Button Text', 'wp-product-review' ) . ' ' . $i,
										)
									);
									?>
								</li>
								<li>
									<label for="rp-editor-button-link-<?php $i; ?>"><?php echo __( 'Affiliate Link', 'wp-product-review' ) . ' ' . $i; ?> </label>
									<?php
									echo $html_helper->text(
										array(
											'name'        => 'rp-editor-button-link-' . $i,
											'value'       => $url,
											'placeholder' => __( 'Affiliate Link', 'wp-product-review' ) . ' ' . $i,
										)
									);
									?>
								</li>
								<?php
							}
							$i ++;
						}
					}
				}
				?>

				<li>
					<label for="rp-editor-price"><?php _e( 'Product Price', 'wp-product-review' ); ?> </label>
					<?php
					echo $html_helper->text(
						array(
							'name'        => 'rp-editor-price',
							'value'       => $review->get_price_raw(),
							'placeholder' => __( 'Product Price', 'wp-product-review' ),
						)
					);
					?>
				</li>

			</ul>
		</div>
		<?php do_action( 'rp_editor_details_after', $model->post ); ?>
	</div><!-- end .review-settings-notice -->
	<div class="rp-review-options rp-review-section">
		<h4><?php _e( 'Product Options', 'wp-product-review' ); ?></h4>
		<p><?php _e( 'Insert your options and their grades. Grading must be done from 0 to 100.', 'wp-product-review' ); ?></p>
		<div class="cwpr_clearfix">
			<?php
			if ( $model->rp_get_option( 'cwppos_show_poweredby' ) === 'yes' || class_exists( 'RP_Pro' ) || function_exists( 'rp_ep_js_preloader' ) ) {
				?>
				<a href="#" class="preload_info"><?php _e( 'Preload Info', 'wp-product-review' ); ?></a>
				<?php
			} else {
				echo '<label class="rp-upsell-label">' . __( ' In order to be able to automatically load your options from another posts, you need the ', 'wp-product-review' ) . '<a href="' . RP_UPSELL_LINK . '" target="_blank" >' . __( 'PRO add-on', 'wp-product-review' ) . '</a></label>';
			}
			?>
		</div>
		<?php do_action( 'rp_editor_options_before', $model->post ); ?>
		<div class="rp-review-fieldset rp-review-options-fields">
			<ul class="rp-review-options-list">
				<?php

				$options_nr = $model->rp_get_option( 'cwppos_option_nr' );
				for ( $i = 1; $i <= $options_nr; $i ++ ) {
					?>
					<li>
						<label for="rp-editor-options-text-<?php echo $i; ?>"><?php echo $i; ?></label>
						<?php
						echo $html_helper->text(
							array(
								'name'        => 'rp-editor-options-name[' . $i . ']',
								'id'          => 'rp-editor-options-name-' . $i,
								'value'       => $model->get_value( 'rp-option-name-' . $i ),
								'placeholder' => __( 'Option', 'wp-product-review' ) . ' ' . $i,
							)
						);
						echo $html_helper->text(
							array(
								'name'        => 'rp-editor-options-value[' . $i . ']',
								'id'          => 'rp-editor-options-value-' . $i,
								'class'       => 'rp-text rp-option-number',
								'value'       => $model->get_value( 'rp-option-value-' . $i ),
								'placeholder' => __( 'Grade', 'wp-product-review' ),
							)
						);
						?>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
		<?php do_action( 'rp_editor_options_after', $model->post ); ?>

	</div>
	<div class="rp-review-pros  rp-review-section">
		<h4><?php _e( 'Pro Features', 'wp-product-review' ); ?></h4>
		<p><?php _e( 'Insert product\'s pro features below.', 'wp-product-review' ); ?></p>

		<?php do_action( 'rp_editor_pros_before', $model->post ); ?>
		<div class="rp-review-fieldset rp-review-pros-fields">
			<ul class="rp-review-options-list ">
				<?php
				$options_nr = $model->rp_get_option( 'cwppos_option_nr' );
				$pros       = $review->get_pros();
				for ( $i = 1; $i <= $options_nr; $i ++ ) {
					?>
					<li>
						<label for="rp-editor-pros-<?php echo $i; ?>"><?php echo $i; ?></label>
						<?php
						echo $html_helper->text(
							array(
								'name'        => 'rp-editor-pros[]',
								'id'          => 'rp-editor-pros-' . $i,
								'value'       => isset( $pros[ $i - 1 ] ) ? $pros[ $i - 1 ] : '',
								'placeholder' => __( 'Option', 'wp-product-review' ) . ' ' . $i,
							)
						);
						?>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
		<?php do_action( 'rp_editor_pros_after', $model->post ); ?>
	</div>
	<div class="rp-review-cons  rp-review-section">
		<h4><?php _e( 'Cons Features', 'wp-product-review' ); ?></h4>
		<p><?php _e( 'Insert product\'s cons features below.', 'wp-product-review' ); ?></p>
		<?php do_action( 'rp_editor_cons_before', $model->post ); ?>
		<div class="rp-review-fieldset rp-review-cons-fields">
			<ul class="rp-review-options-list ">
				<?php
				$options_nr = $model->rp_get_option( 'cwppos_option_nr' );
				$cons       = $review->get_cons();
				for ( $i = 1; $i <= $options_nr; $i ++ ) {
					?>
					<li>
						<label for="rp-editor-cons-<?php echo $i; ?>"><?php echo $i; ?></label>
						<?php
						echo $html_helper->text(
							array(
								'name'        => 'rp-editor-cons[]',
								'id'          => 'rp-editor-cons-' . $i,
								'value'       => isset( $cons[ $i - 1 ] ) ? $cons[ $i - 1 ] : '',
								'placeholder' => __( 'Option', 'wp-product-review' ) . ' ' . $i,
							)
						);
						?>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
		<?php do_action( 'rp_editor_cons_after', $model->post ); ?>
	</div>

	<br class="clear">

	<?php if ( ! shortcode_exists( 'P_REVIEW' ) ) : ?>
		<label class="rp-upsell-label"> You can use the shortcode <b>[P_REVIEW]</b> to show a review you already made
			or
			<b>[wpr_landing]</b> to display a comparision table of them. The shortcodes are available on the <a
					target="_blank" href="<?php echo RP_UPSELL_LINK; ?>">Pro Bundle</a><br/><br/></label>
	<?php endif; ?>

	<?php do_action( 'rp_editor_after', $model->post ); ?>
</div>

<script id="rp-review-type-fields-template" type="text/template" 
	data-json='<?php echo str_replace( "'", '\"', json_encode( $schema_types ) ); ?>'
	data-type='<?php echo $review->get_type(); ?>'
	data-custom-fields='<?php echo json_encode( $review->get_custom_fields() ); ?>'
>
	<li class="rp-review-type-field">
		<label for="rp-editor-review-type-field">#name#</label>
		<input type="text" name="#name#" value="#value#" class="regular-text">
		<input type="hidden" name="rp-editor-review-type-field[]" value="#name#">
		<p class="desc">#desc#</p>
	</li>
</script>
