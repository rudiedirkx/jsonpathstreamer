<?php

use JsonStreamingParser\Parser;
use rdx\jsonpathstreamer\RegexConfigJsonListener;

require __DIR__ . '/../vendor/autoload.php';

class SaveAllPathListener extends RegexConfigJsonListener {

	public function getRules() {
		return [
			'#^users/[^/]+/(num|name)(/|$)#',
		];
	}

}

header('Content-type: text/plain');

$stream = fopen('example.json', 'r');

$listener = new SaveAllPathListener;

$parser = new Parser($stream, $listener);
$parser->parse();

print_r($listener->getValue());
