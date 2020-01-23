<?php

?>
<div class="rp-prodlist">
	<?php
	foreach ( $results as $review ) :

		$review_object = new RP_Review_Model( $review['ID'] );
		$product_image = $review_object->get_small_thumbnail();
		$product_title = ( $instance['post_type'] == true ) ? $review_object->get_name() : get_the_title( $review['ID'] );
		$product_title_display = $product_title;
		if ( strlen( $product_title_display ) > $title_length ) {
			$product_title_display = substr( $product_title_display, 0, $title_length ) . '...';
		}
		$links          = $review_object->get_links();
		$affiliate_link = reset( $links );
		$review_link    = get_the_permalink( $review['ID'] );

		$showingImg = $instance['show_image'] == true && ! empty( $product_image );
		?>

		<div class="rp-prodrow">
			<?php if ( $showingImg ) { ?>
				<div class="rp-prodrowleft">
					<a href="<?php echo $review_link; ?>" class="rp-col" title="<?php echo $product_title; ?>" rel="noopener">
						<img class="cwp_rev_image rp-col" src="<?php echo $product_image; ?>"
						     alt="<?php echo $product_title; ?>"/>
					</a>
				</div>
				<?php
			}
			?>
			<div class="rp-prodrowright <?php echo $showingImg ? 'rp-prodrowrightadjust' : '' ?>">
				<p><strong><?php echo $product_title_display; ?></strong></p>
				<?php rp_layout_get_rating( $review_object, 'stars', 'style1-widget' ); ?>
				<p class="rp-style1-buttons">
					<?php
					$link = "<a href='{$affiliate_link}' rel='nofollow' target='_blank' class='rp-bttn'>" . __( $instance['cwp_tp_buynow'], 'wp-product-review' ) . '</a>';
					if ( ! empty( $instance['cwp_tp_buynow'] ) ) {
						echo apply_filters( 'rp_widget_style1_buynow_link', $link, $review['ID'], $affiliate_link, $instance['cwp_tp_buynow'] );
					}

					$link = "<a href='{$review_link}' rel='nofollow' target='_blank' class='rp-bttn'>" . __( $instance['cwp_tp_readreview'], 'wp-product-review' ) . '</a>';
					if ( ! empty( $instance['cwp_tp_readreview'] ) ) {
						echo apply_filters( 'rp_widget_style1_readreview_link', $link, $review['ID'], $review_link, $instance['cwp_tp_readreview'] );
					}
					?>
				</p>
			</div>
			<div class="clear"></div>
		</div>
	<?php endforeach; ?>
</div>
