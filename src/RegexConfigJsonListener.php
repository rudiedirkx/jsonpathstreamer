<?php

namespace rdx\jsonpathstreamer;

abstract class RegexConfigJsonListener extends PathAwareJsonListener {

	protected $regexes = [];

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->regexes = $this->getRules();
	}

	/**
	 * Implements PathAwareJsonListener::gotPath().
	 */
	public function gotPath( array $path ) {
		// Ignore empty non-scalars
	}

	/**
	 * Implements PathAwareJsonListener::gotValue().
	 */
	public function gotValue( array $path, $value ) {
		foreach ( $this->regexes as $regex ) {
			if ( preg_match($regex, implode($this->separator, $path)) ) {
				return $this->rememberValue($path, $value);
			}
		}
	}

	/**
	 * Get the configured rules: regexes of savable paths.
	 *
	 * @return array
	 */
	abstract public function getRules();

}
