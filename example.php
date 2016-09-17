<?php

use rdx\jsonpathstreamer\PathAwareJsonListener;

require __DIR__ . '/vendor/autoload.php';

class SaveAllPathListener extends PathAwareJsonListener {

	protected $value = [];

	public function gotPath(array $path) {
		echo implode(' > ', $path) . "\n";
	}

	public function gotValue(array $path, $value) {
		if (array_slice($path, 0, 2) == ['users', 'rudie']) {
			$this->setValue($this->value, array_slice($path, 2), $value);
		}
	}

}

header('Content-type: text/plain');

$stream = fopen('example.json', 'r');

$listener = new SaveAllPathListener;

$parser = new \JsonStreamingParser\Parser($stream, $listener);
$parser->parse();

echo "\n\n";

print_r($listener->getValue());
