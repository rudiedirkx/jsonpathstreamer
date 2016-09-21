<?php

namespace rdx\jsonpathstreamer;

use rdx\jsonpathstreamer\PathAwareJsonListener;

abstract class RegexTargetConfigJsonListener extends PathAwareJsonListener {

	protected $regexes = [];
	protected $separator = '/';

	/**
	 * Implements JsonStreamingParser\Listener::startDocument().
	 */
	public function startDocument() {
		$this->regexes = $this->getRules();
	}

	/**
	 * Implements PathAwareJsonListener::gotPath().
	 */
	public function gotPath(array $path) {
		// Ignore empty non-scalars
	}

	/**
	 * Implements PathAwareJsonListener::gotValue().
	 */
	public function gotValue(array $path, $value) {
		foreach ($this->regexes as $regex => $target) {
			$pathString = implode($this->separator, $path);
			if (($targetString = preg_replace($regex, $target, $pathString)) != $pathString) {
				$target = explode($this->separator, $targetString);
				return $this->rememberValue($target, $value);
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
