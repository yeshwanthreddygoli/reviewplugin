<?php
/**
 * RP Template 1.
 *
 * @package     RP
 * @subpackage  Layouts
 * @copyright   Copyright (c) 2017, Bogdan Popa
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
?>
<div class="rp-template rp-template-1 <?php echo is_rtl() ? 'rtl' : ''; ?>">

	<?php
	$review_id     = $review_object->get_ID();
	$review_pros   = $review_object->get_pros();
	$review_cons   = $review_object->get_cons();
	?>

	<div id="rp-review-<?php echo $review_id; ?>" class="rp-review-container">

		<h2 class="rp-review-name"><?php echo esc_html( $review_object->get_name() ); ?></h2>

	<?php rp_layout_get_rating( $review_object, 'stars', 'style1', '', false ); ?>

		<div class="rp-review-grade">
			<div class="rp-review-grade-number">
				<?php rp_layout_get_rating( $review_object, 'number', 'style1' ); ?>
			</div>
			<div class="rp-review-product-image">
				<?php rp_layout_get_image( $review_object, 'rp-default-img', 'rp-product-image' ); ?>
			</div>

			<?php rp_layout_get_options_ratings( $review_object, 'bars' ); ?>

		</div><!-- end .rp-review-grade -->

		<div class="rp-review-pros-cons<?php echo ( $review_pros && $review_cons ) ? '' : ' rp-review-one-column'; ?>">
			<?php rp_layout_get_pros( $review_object, '', 'h3', 'rp-review-pros-name' ); ?>
			<?php rp_layout_get_cons( $review_object, '', 'h3', 'rp-review-cons-name' ); ?>
		</div><!-- end .rp-review-pros-cons -->

	</div><!-- end .rp-review-container -->
	<?php rp_layout_get_affiliate_buttons( $review_object ); ?>

</div>
