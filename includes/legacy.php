<?php

function cwppos_show_review( $post_id ) {
	$plugin        = new RP();
	$review_object = new RP_Review_Model( $post_id );
	$public        = new rp_Public( $plugin->get_plugin_name(), $plugin->get_version() );
	$public->load_review_assets( $review_object );
	$output = '';
	if ( $review_object->is_active() ) {
		$template = new RP_Template();
		$output  .= $template->render(
			'default',
			array(
				'review_object' => $review_object,
			),
			false
		);

		$output .= $template->render(
			'rich-json-ld',
			array(
				'review_object' => $review_object,
			),
			false
		);
	}

	return $output;
}


function cwppos( $option = null ) {
	$options = new RP_Options_Model();

	if ( is_null( $option ) ) {
		return $options->get_all();
	}
	return $options->rp_get_option( $option );
}
