<?php

use JsonStreamingParser\Parser;
use rdx\jsonpathstreamer\DoneParsingException;
use rdx\jsonpathstreamer\PathAwareJsonListener;

require __DIR__ . '/../vendor/autoload.php';

class SaveAllPathListener extends PathAwareJsonListener {

	public function gotPath(array $path) {
		// Ignore
	}

	public function gotValue(array $path, $value) {
		// Remember everything!
		$this->rememberValue($path, $value);
	}

	public function stopAfter() {
		// Only require users "rudie" and "mary", then stop
		return [
			'#^users/rudie/#',
			'#^users/mary/#',
		];
	}

}

header('Content-type: text/plain');

$stream = fopen('example.json', 'r');

$listener = new SaveAllPathListener;

try {
	$parser = new Parser($stream, $listener);
	$parser->parse();
}
catch (DoneParsingException $ex) {
	if ($ex->allTouched) {
		echo "Stopping prematurely because all stopAfter() conditions have been met\n\n";
	}
	else {
		echo "Have reached end of document without meeting all stopAfter() conditions...\n\n";
	}
}

print_r($listener->getValue());
