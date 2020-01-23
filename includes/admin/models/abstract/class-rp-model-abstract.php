<?php

class RP_Model_Abstract {

	
	public $logger;
	
	private $options;
	
	private $namespace = 'cwppos_options';

	
	public function __construct() {
		$this->options = get_option( $this->namespace, array() );
		$this->logger  = new RP_Logger();
	}

	
	public function rp_get_option( $key = '' ) {
		return $this->get_var( $key );
	}

	protected function get_var( $key ) {
		$this->logger->notice( 'Getting value for ' . $key );
		if ( isset( $this->options[ $key ] ) ) {
			return apply_filters( 'rp_get_old_option', $this->options[ $key ], $key );
		}
		$default = $this->get_default( $key );

		return apply_filters( 'rp_get_default_option', $default, $key );
	}

	
	private function get_default( $key ) {
		$settings = RP_Global_Settings::instance()->get_fields();
		$all      = array();
		foreach ( $settings as $section ) {
			$all = array_merge( $all, $section );
		}
		if ( ! isset( $all[ $key ] ) ) {
			return false;
		}
		if ( ! isset( $all[ $key ]['default'] ) ) {
			return false;
		}

		return $all[ $key ]['default'];
	}

	
	public function get_all() {
		return $this->options;
	}

	
	public function rp_set_option( $key = '', $value = '' ) {
		return $this->set_var( $key, $value );
	}

	
	protected function set_var( $key, $value = '' ) {
		$this->logger->notice( 'Setting value for ' . $key . ' with ' . print_r( $value, true ) );
		if ( ! array_key_exists( $key, $this->options ) ) {
			$this->options[ $key ] = '';
		}
		$this->options[ $key ] = apply_filters( 'rp_pre_option_' . $key, $value );

		return update_option( $this->namespace, $this->options );
	}
}
