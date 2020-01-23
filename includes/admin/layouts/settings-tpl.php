<?php


$global_settings = RP_Global_Settings::instance();
$sections        = $global_settings->get_sections();
$fields          = $global_settings->get_fields();

?>
<div id="rp-admin">
	<?php do_action( 'rp_admin_page_before' ); ?>

	<?php include RP_PATH . '/includes/admin/layouts/header-part.php'; ?>

	<div id="rp_top_tabs" class="clearfix">
		<ul id="tabs_menu" role="menu">
			<?php foreach ( $sections as $section_key => $section_name ) : ?>
				<li class="rp-nav-tab" id="rp-nav-tab-<?php echo $section_key; ?>"
					data-tab="rp-tab-<?php echo $section_key; ?>">
					<a href="#rp-tab-<?php echo $section_key; ?>" title="<?php esc_attr( $section_name ); ?>">
						<?php echo esc_html( $section_name ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

	</div>
	<form id="rp-settings" method="post" action="#" enctype="multipart/form-data">

		<?php foreach ( $sections as $section_key => $section_name ) : ?>
			<div id="rp-tab-<?php echo $section_key; ?>" class="rp-tab-content">
				<?php
				if ( ! shortcode_exists( 'P_REVIEW' ) ) {
					?>
				<label class="rp-upsell-label"> You can use the shortcode <b>[P_REVIEW]</b> to show a review you
				already made or
				<b>[wpr_landing]</b> to display a comparison table of them. The shortcodes are available on the
				<a
						target="_blank" href="<?php echo RP_UPSELL_LINK; ?>">Pro Bundle</a><br/><br/></label>
					<?php
				} else {
					do_action( 'rp_settings_section_upsell', $section_key );
				}
				foreach ( $fields[ $section_key ] as $name => $field ) {
					$field['title'] = $field['name'];
					$field['name']  = $name;
					$field['value'] = $model->rp_get_option( $name );
					$this->add_element( $field );
				}
				?>
			</div>

		<?php endforeach; ?>

		<div id="info_bar">
			<button type="button"
					class="button-primary cwp_save"><?php _e( 'Save All Changes', 'wp-product-review' ); ?></button>
			<span class="spinner"></span>
		</div><!--.info_bar-->
		<?php wp_nonce_field( 'rp_save_global_settings', 'rp_nonce_settings', false ); ?>
	</form>
	<?php do_action( 'rp_admin_page_after' ); ?>
</div>
