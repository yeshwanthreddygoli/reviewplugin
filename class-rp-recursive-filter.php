<?php

class rp_Recursive_Filter extends RecursiveFilterIterator {

	
	public $callback;


	public function __construct( RecursiveIterator $iterator, $callback ) {
		$this->callback = $callback;
		parent::__construct( $iterator );
	}


	public function accept() {
		$callback = $this->callback;

		return call_user_func_array( $callback, array( parent::current(), parent::key(), parent::getInnerIterator() ) );
	}


	public function getChildren() {
		return new self( $this->getInnerIterator()->getChildren(), $this->callback );
	}
}

