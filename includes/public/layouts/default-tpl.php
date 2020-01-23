<?php


ob_start();
include_once RP_PATH . '/includes/public/layouts/' . $review_object->get_template() . '.php';
if ( ! isset( $output ) ) {
	$output = '';
}
$output .= ob_get_contents();

ob_end_clean();
