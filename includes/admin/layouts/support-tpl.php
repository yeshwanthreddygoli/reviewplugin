<div id="rp-admin" class="rp-settings">

	<?php include RP_PATH . '/includes/admin/layouts/header-part.php'; ?>

	<?php
	$active_tab  = isset( $_REQUEST['tab'] ) ? sanitize_text_field( $_REQUEST['tab'] ) : 'help';
	$show_more = ! defined( 'RP_PRO_VERSION' );
	?>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=rp-support&tab=help' ) ); ?>"
		   class="nav-tab <?php echo $active_tab === 'help' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Support', 'wp-product-review' ); ?></a>
		<?php
		if ( $show_more ) {
			?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=rp-support&tab=more' ) ); ?>"
	   class="nav-tab <?php echo $active_tab === 'more' ? 'nav-tab-active' : ''; ?>"><?php _e( 'More Features', 'wp-product-review' ); ?></a>
			<?php
		}
		?>
	</h2>

	<div class="rp-features-content">
		<div class="rp-feature">
			<div class="rp-feature-features">
					<?php
					switch ( $active_tab ) {
						case 'help':
								include RP_PATH . '/includes/admin/layouts/support-tab.php';
							break;
						case 'more':
							if ( $show_more ) {
								include RP_PATH . '/includes/admin/layouts/upsell-tab.php';
							}
							break;
					}
					?>
				<div class="clear"></div>
			</div>
		</div>
	</div>

</div>
