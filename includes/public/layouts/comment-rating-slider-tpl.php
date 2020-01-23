<?php
		$options      = $this->review->get_options();
		$option_names = wp_list_pluck( $options, 'name' );
		$sliders      = array();

		foreach ( $option_names as $k => $value ) {
	$sliders[] =
'<div class="rp-comment-form-meta ' . ( is_rtl() ? 'rtl' : '' ) . '">
            <label for="rp-slider-option-' . $k . '">' . $value . '</label>
            <input type="text" id="rp-slider-option-' . $k . '" class="meta_option_input" value="" name="rp-slider-option-' . $k . '" readonly="readonly">
            <div class="rp-comment-meta-slider"></div>
            <div class="cwpr_clearfix"></div>
		</div>';
		}

		$scale      = $this->review->rp_get_option( 'rp_use_5_rating_scale' );
		if ( empty( $scale ) ) {
	$scale  = 10;
		}

		echo '<input type="hidden" name="rp-scale" value="' . $scale . '">';
		echo '<div id="rp-slider-comment">' . implode( '', $sliders ) . '<div class="cwpr_clearfix"></div></div>';


