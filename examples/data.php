<?php

use JsonStreamingParser\Parser;
use rdx\jsonpathstreamer\PathAwareJsonListener;

require __DIR__ . '/../vendor/autoload.php';

class SaveAllPathListener extends PathAwareJsonListener {

	protected $userPaths = [];

	public function gotPath(array $path) {
		// Ignore empty non-scalars
	}

	public function gotValue(array $path, $value) {
		if (count($path) > 2 && $path[0] == 'users') {
			$_path = $path[0] . '/' . $path[1];

			// When we encouter a user's "save", remember it
			if ($path[2] == 'save') {
				$this->userPaths[$_path] = (bool) $value;
				return;
			}

			// Until we know it, or if it's truthy, save all user content
			if (!isset($this->userPaths[$_path]) || $this->userPaths[$_path]) {
				$this->rememberValue($path, $value);
			}
			// Or remove all remembered buffer for this user
			else {
				unset($this->value['users'][ $path[1] ]);
			}
		}
	}

}

header('Content-type: text/plain');

$stream = fopen('example.json', 'r');

$listener = new SaveAllPathListener;

$parser = new Parser($stream, $listener);
$parser->parse();

print_r($listener->getValue());
