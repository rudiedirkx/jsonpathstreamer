<?php

namespace rdx\jsonpathstreamer;

use JsonStreamingParser\Listener\IdleListener;

abstract class PathAwareJsonListener extends IdleListener {

	protected $stopAfter = [];
	protected $separator = '/';

	protected $path = [];
	protected $value = [];

	protected $indent = -1;
	protected $array = [false];
	protected $array_index = [-1];
	protected $lastKey = null;
	protected $trace = [];
	protected $key = [];

	/**
	 *
	 */
	public function __construct() {
		if ( $regexes = $this->stopAfter() ) {
			$this->stopAfter = array_combine($regexes, array_fill(0, count($regexes), false));
		}
	}

	/**
	 * Implements JsonStreamingParser\Listener::endDocument().
	 */
	public function endDocument() : void {
		if ( !empty($this->stopAfter) ) {
			$this->stop(false);
		}
	}

	/**
	 * Implements JsonStreamingParser\Listener::startObject().
	 */
	public function startObject() : void {
		$this->arrayKey();

		$this->indent++;
		$this->array[$this->indent] = false;
		if ( $this->lastKey !== null ) {
			$this->trace[] = $this->lastKey;
		}
	}

	/**
	 * Implements JsonStreamingParser\Listener::endObject().
	 */
	public function endObject() : void {
		$this->indent--;
		array_pop($this->trace);
	}

	/**
	 * Implements JsonStreamingParser\Listener::startArray().
	 */
	public function startArray() : void {
		$this->arrayKey();

		$this->indent++;
		$this->array[$this->indent] = true;
		$this->array_index[$this->indent] = -1;
		if ( $this->lastKey !== null ) {
			$this->trace[] = $this->lastKey;
		}
	}

	/**
	 * Implements JsonStreamingParser\Listener::endArray().
	 */
	public function endArray() : void {
		$this->array[$this->indent] = false;
		$this->indent--;
		array_pop($this->trace);
	}

	/**
	 * Implements JsonStreamingParser\Listener::key().
	 */
	public function key( string $key ) : void {
		$this->lastKey = $key;
		$this->composeKey();

		$this->gotPath($this->path);
	}

	/**
	 * Implements JsonStreamingParser\Listener::value().
	 */
	public function value( $value ) : void {
		$this->arrayKey();
		$this->composeKey();

		$this->touch($this->path);

		$this->gotValue($this->path, $value);
	}

	/**
	 * Remember this path was visited, to know when it's been parsed completely.
	 */
	protected function touch( array $path ) {
		if ( !empty($this->stopAfter) ) {
			$pathString = implode($this->separator, $path);
			foreach ( $this->stopAfter as $regex => &$touched ) {
				if ( preg_match($regex, $pathString) ) {
					$touched = true;
				}
				elseif ( $touched ) {
					unset($this->stopAfter[$regex]);

					if ( empty($this->stopAfter) ) {
						$this->stop(true);
					}
				}

				unset($touched);
			}
		}
	}

	/**
	 * Stop parsing entirely.
	 *
	 * @throws DoneParsingException
	 */
	protected function stop( $allTouched ) {
		throw new DoneParsingException($allTouched);
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
		if ( !empty($this->array[$this->indent]) ) {
			$this->array_index[$this->indent]++;
			$this->key($this->array_index[$this->indent]);
		}
	}

	/**
	 * Optional helper to save a scalar value into a non-scalar path.
	 */
	protected function setValue( array &$container, array $key, $value ) {
		$element = &$container;
		foreach ( $key as $subkey ) {
			$element = &$element[$subkey];
		}
		$element = $value;
	}

	/**
	 * Remember a value into the provided value property.
	 */
	protected function rememberValue( array $path, $value ) {
		$this->setValue($this->value, $path, $value);
	}

	/**
	 * Return the entire saved value.
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Define a list of paths (regexes) that must be completed. Completely stops
	 * the parser after these.
	 *
	 * @return array
	 */
	public function stopAfter() {
		// Optional
	}

	/**
	 * Get notified about a new key, value still unknown.
	 *
	 * @return void
	 */
	abstract public function gotPath( array $path );

	/**
	 * Get notified about a new value, including complete path.
	 *
	 * @return void
	 */
	abstract public function gotValue( array $path, $value );

}
