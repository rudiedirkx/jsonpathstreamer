<?php

use JsonStreamingParser\Parser;
use rdx\jsonpathstreamer\RegexTargetConfigJsonListener;

require __DIR__ . '/../vendor/autoload.php';

class SaveAllPathListener extends RegexTargetConfigJsonListener {

	public function getRules() {
		return [
			'#^packages/(\d+)/name$#' => '$1',
			'#^features/(\d+)/properties/STREET$#' => '$1/street',
		];
	}

	protected function rememberValue( array $path, $value ) {
		parent::rememberValue($path, $value);
		echo "saved " . implode('/', $path) . " (" . number_format(memory_get_peak_usage()/1e6, 1) . " MB)\n";
	}

}

header('Content-type: text/plain');

if ( empty($_SERVER['argv']) ) {
	exit("You must run this on the commandline, because it'll process 200 MB of JSON.\n");
}

$stream = fopen('https://raw.githubusercontent.com/zemirco/sf-city-lots-json/master/citylots.json', 'r');

$listener = new SaveAllPathListener;

$_time = microtime(1);

$parser = new Parser($stream, $listener);
$parser->parse();

$_time = microtime(1) - $_time;

print_r($listener->getValue());

echo "\n" . number_format($_time * 1000, 1) . " ms\n";
