<?php


if ( $review_object->rp_get_option( 'rp_rich_snippet' ) == 'yes' ) {
	?>
	<script type="application/ld+json"><?php echo json_encode( $review_object->get_json_ld() ); ?></script>
	<?php
}
