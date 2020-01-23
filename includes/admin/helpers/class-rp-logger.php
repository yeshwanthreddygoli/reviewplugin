<?php

class RP_Logger {

	
	public function warning( $msg = '' ) {
		$this->message( $msg, 'warning' );
	}


	private function message( $msg, $type ) {

		if ( ! defined( 'RP_DEBUG' ) ) {
			return;
		}
		if ( ! RP_DEBUG ) {
			return;
		}
		$type   = strtoupper( $type );
		$msg    = $type . ' : ' . $msg;
		$bt     = debug_backtrace();
		$caller = array_shift( $bt );
		$caller = array_shift( $bt );
		$caller = array_shift( $bt );
		$msg    = $msg . ' [ ' . $caller['file'];
		$msg    = $msg . ' : ' . $caller['line'] . ' ]';
		error_log( $msg );

	}

	
	public function error( $msg = '' ) {
		$this->message( $msg, 'error' );
	}

	
	public function notice( $msg = '' ) {
		$this->message( $msg, 'notice' );
	}
}
