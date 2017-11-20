<?php

use JsonStreamingParser\Parser;
use rdx\jsonpathstreamer\RegexTargetConfigJsonListener;

require __DIR__ . '/../vendor/autoload.php';

class SaveAllPathListener extends RegexTargetConfigJsonListener {

	public function getRules() {
		return [
			'#^users/([^/]+)/(name)(/|$)#' => 'entities/$1/$2',
			'#^offices/([^/]+)/(name)(/|$)#' => 'entities/$1/$2',
		];
	}

}

header('Content-type: text/plain');

$stream = fopen(__DIR__ . '/example.json', 'r');

$listener = new SaveAllPathListener;

$parser = new Parser($stream, $listener);
$parser->parse();

print_r($listener->getValue());
