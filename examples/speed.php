<?php

use JsonStreamingParser\Parser;
use rdx\jsonpathstreamer\PathAwareJsonListener;

require __DIR__ . '/../vendor/autoload.php';

class SaveAllPathListener extends PathAwareJsonListener {

	function gotPath(array $path) {
		// Save empty non-scalars too, because json_decode() does that
		$this->rememberValue($path, []);
	}

	function gotValue(array $path, $value) {
		$this->rememberValue($path, $value);
	}

}

header('Content-type: text/plain');

// Streaming
$_time = microtime(1);

$stream = fopen('example.json', 'r');

$listener = new SaveAllPathListener;

$parser = new Parser($stream, $listener);
$parser->parse();

$time1 = number_format((microtime(1) - $_time) * 1000, 2);

// Native decoding
$_time = microtime(1);

$value = json_decode(file_get_contents('example.json'), true);

$time2 = number_format((microtime(1) - $_time) * 1000, 2);

// Timing
echo "streaming : $time1 ms\n";
echo "native    : $time2 ms\n";

echo "\n";

// Results

echo "streaming:\n";
print_r($listener->getValue());

echo "\nnative:\n";
print_r($value);

echo "\nidentical:\n";
var_dump($listener->getValue() == $value);
