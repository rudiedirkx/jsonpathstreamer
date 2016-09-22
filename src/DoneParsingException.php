<?php

namespace rdx\jsonpathstreamer;

class DoneParsingException extends \Exception {

	public $allTouched;

	public function __construct($allTouched) {
		$this->allTouched = $allTouched;
	}

}
