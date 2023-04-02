<?php

use JsonStreamingParser\Parser;
use rdx\jsonpathstreamer\PathAwareJsonListener;

require __DIR__ . '/../vendor/autoload.php';

class RecorderParser extends Parser {

}

class SaveAllPathListener extends PathAwareJsonListener {

	public function gotPath(array $path) {
		// echo implode(' > ', $path) . "\n";
	}

	public function startObject() : void {
		parent::startObject();
echo "  object\n";
echo "> " . implode(' > ', $this->path) . "\n\n";
	}

	public function endObject() : void {
		// @todo Keep keeping track of path after closing objects/arrays
		$this->composeKey();

		parent::endObject();

echo "< " . implode(' > ', $this->path) . "\n\n";

	}

	public function gotValue(array $path, $value) {
		// var_dump($value);
	}

}

header('Content-type: text/plain');

$stream = fopen(__DIR__ . '/example.json', 'r');

$listener = new SaveAllPathListener;

$parser = new RecorderParser($stream, $listener);
$parser->parse();

print_r($listener->getValue());
