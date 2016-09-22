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

See `examples/` for more examples. Run `examples/speed.php` for a speed comparison.

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

		// Optional
		public function stopAfter() {
			// Stop parsing after foo/bar because there's nothing I want there
			return ['#foo/bar/#'];
		}
	}

Configurable - easy
----

	// MUST implement getRules()
	class MyListener extends \rdx\jsonpathstreamer\RegexConfigJsonListener {
		public function getRules() {
			// Save only "name", for all users into their original position
			return [
				'#^users/[^/]+/(name)(/|$)#',
				'#^offices/[^/]+/(name)(/|$)#',
			];
		}
	}

Configurable - conversion
----

	// MUST implement getRules()
	class MyListener extends \rdx\jsonpathstreamer\RegexTargetConfigJsonListener {
		public function getRules() {
			// Save only "name", for all users and offices, into the same list
			return [
				'#^users/([^/]+)/(name)(/|$)#' => 'entities/$1/$2',
				'#^offices/([^/]+)/(name)(/|$)#' => 'entities/$1/$2',
			];
		}
	}

