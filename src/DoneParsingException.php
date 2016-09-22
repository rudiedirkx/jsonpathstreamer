<?php

namespace rdx\jsonpathstreamer;

class DoneParsingException extends \Exception {

	public $allTouched;

	/**
	 * Construct exception with only 1 relevant bool
	 */
	public function __construct( $allTouched ) {
		$this->allTouched = $allTouched;
	}

}
