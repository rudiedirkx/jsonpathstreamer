<?php

namespace rdx\jsonpathstreamer;

use JsonStreamingParser\Listener\IdleListener;

abstract class PathAwareJsonListener extends IdleListener {

	protected $indent = -1;
	protected $array = [false];
	protected $array_index = [-1];
	protected $lastKey = null;
	protected $trace = [];
	protected $key = [];

	/**
	 * Implements JsonStreamingParser\Listener::startObject().
	 */
	public function startObject() {
		$this->arrayKey();

		$this->indent++;
		$this->array[$this->indent] = false;
		if ($this->lastKey !== null) {
			$this->trace[] = $this->lastKey;
		}
	}

	/**
	 * Implements JsonStreamingParser\Listener::endObject().
	 */
	public function endObject() {
		$this->indent--;
		array_pop($this->trace);
	}

	/**
	 * Implements JsonStreamingParser\Listener::startArray().
	 */
	public function startArray() {
		$this->arrayKey();

		$this->indent++;
		$this->array[$this->indent] = true;
		$this->array_index[$this->indent] = -1;
		if ($this->lastKey !== null) {
			$this->trace[] = $this->lastKey;
		}
	}

	/**
	 * Implements JsonStreamingParser\Listener::endArray().
	 */
	public function endArray() {
		$this->array[$this->indent] = false;
		$this->indent--;
		array_pop($this->trace);
	}

	/**
	 * Implements JsonStreamingParser\Listener::key().
	 */
	public function key($key) {
		$this->lastKey = $key;
		$this->composeKey();

		$this->gotPath($this->path);
	}

	/**
	 * Implements JsonStreamingParser\Listener::value().
	 */
	public function value($value) {
		$this->arrayKey();
		$this->composeKey();

		$this->gotValue($this->path, $value);
	}

	/**
	 * Create a complete key from the trace and current level.
	 */
	protected function composeKey() {
		$this->path = array_merge($this->trace, [$this->lastKey]);
	}

	/**
	 * Trigger a new key, numeric or associative.
	 */
	protected function arrayKey() {
		if (!empty($this->array[$this->indent])) {
			$this->array_index[$this->indent]++;
			$this->key($this->array_index[$this->indent]);
		}
	}

	/**
	 * Optional helper to save a scalar value into a non-scalar path.
	 */
	protected function setValue(array &$container, array $key, $value) {
		$element = &$container;
		foreach ($key as $subkey) {
			$element =& $element[$subkey];
		}
		$element = $value;
	}

	/**
	 * Remember a value into the provided value property.
	 */
	protected function rememberValue(array $path, $value) {
		$this->setValue($this->value, $path, $value);
	}

	/**
	 * Return the entire saved value.
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Get notified about a new key, value still unknown.
	 */
	abstract public function gotPath(array $path);

	/**
	 * Get notified about a new value, including complete path.
	 */
	abstract public function gotValue(array $path, $value);

}
