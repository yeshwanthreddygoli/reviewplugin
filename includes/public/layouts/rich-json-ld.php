<?php
/**
 *  WP Prodact Review front page layout.
 *
 * @package     RP
 * @subpackage  Layouts
 * @global      RP_Review_Model $review_object The review object.
 * @copyright   Copyright (c) 2017, Bogdan Preda
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

if ( $review_object->rp_get_option( 'rp_rich_snippet' ) == 'yes' ) {
	?>
	<script type="application/ld+json"><?php echo json_encode( $review_object->get_json_ld() ); ?></script>
	<?php
}
