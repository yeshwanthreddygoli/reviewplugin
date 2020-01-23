<?php
/**
 * RP Template 2.
 *
 * @package     RP
 * @subpackage  Layouts
 * @copyright   Copyright (c) 2017, Bogdan Popa
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 * @global      RP_Review_Model $review_object
 */
?>
<div class="rp-template rp-template-2 <?php echo is_rtl() ? 'rtl' : ''; ?>">
	<?php
	$review_id         = $review_object->get_ID();
	$review_pros       = $review_object->get_pros();
	$review_cons       = $review_object->get_cons();
	$review_image      = $review_object->get_small_thumbnail();
	?>

	<div id="rp-review-<?php echo $review_id; ?>" class="rp-review-container">
		<h2 class="rp-review-name"><?php echo esc_html( $review_object->get_name() ); ?></h2>
		<div class="rp-review-head<?php echo ( $review_pros && $review_cons ) ? ' rp-review-with-pros-cons' : ''; ?><?php echo ( $review_image ) ? ' rp-review-with-image' : ''; ?>">
			<div class="rp-review-rating <?php echo is_rtl() ? 'rtl' : ''; ?>">
				<?php rp_layout_get_rating( $review_object, 'number', 'style2' ); ?>
				<?php rp_layout_get_image( $review_object, 'rp-review-product-image rp-default-img', 'rp-product-image' ); ?>
				<?php rp_layout_get_user_rating( $review_object ); ?>
			</div>

			<?php rp_layout_get_pros( $review_object, 'rp-review-pros', 'h3', 'rp-review-pros-name' ); ?>
			<?php rp_layout_get_cons( $review_object, 'rp-review-pros', 'h3', 'rp-review-cons-name' ); ?>

		</div><!-- end .rp-review-head -->

		<?php rp_layout_get_options_ratings( $review_object, 'stars' ); ?>

	</div><!-- end .rp-review-container -->

	<?php rp_layout_get_affiliate_buttons( $review_object ); ?>

</div>
