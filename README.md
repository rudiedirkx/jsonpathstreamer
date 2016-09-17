JSON Path Streamer
====

Uses [salsify/jsonstreamingparser](https://github.com/salsify/jsonstreamingparser)
to parse a JSON file, and stream its tokens. This package adds
features to simplify that process.

In a very big JSON file, it's very important to know where
the parser is, to know which few parts to keep. It provides
an interface for that. For very simple JSON parsing, there's
even a configurable method, without any more parse/JSON
formatting logic.

See `examples/` for more examples.

DIY - Surgical precision
----

	// MUST implement gotPath() and gotValue()
	class MyListener extends \rdx\jsonpathstreamer\PathAwareJsonListener {
		public function gotPath(array $path) {
			// Ignore valueless paths (empty arrays etc)
		}

		public function gotValue(array $path, $value) {
			// Save only values within {"foo": {"bar": {...}}}
			if (array_slice($path, 0, 2) == ['foo', 'bar']) {
				// Ignore long "description" texts
				if (end($path) != 'description') {
					$this->rememberValue(array_slice($path, 2), $value);
				}
			}
		}
	}

Configurable - Probably less space efficient
----

@TODO
