<?php

use JsonStreamingParser\Parser;
use rdx\jsonpathstreamer\RegexTargetConfigJsonListener;

require __DIR__ . '/../vendor/autoload.php';

class SaveAllPathListener extends RegexTargetConfigJsonListener {

	public function getRules() {
		return [
			'#^users/([^/]+)/(num|name)(/|$)#' => '$1/$2',
			'#^offices/([^/]+)/(street|name)(/|$)#' => '$1/$2',
		];
	}

}

header('Content-type: text/plain');

$stream = fopen('example.json', 'r');

$listener = new SaveAllPathListener;

$parser = new Parser($stream, $listener);
$parser->parse();

print_r($listener->getValue());
