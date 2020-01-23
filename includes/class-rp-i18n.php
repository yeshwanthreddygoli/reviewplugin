<?php

class RP_I18n {


	
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wp-product-review',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
