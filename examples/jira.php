<?php

use JsonStreamingParser\Parser;
use rdx\jsonpathstreamer\RegexConfigJsonListener;

require __DIR__ . '/../vendor/autoload.php';

class SaveAllPathListener extends RegexConfigJsonListener {

	public function getRules() {
		return [
			'#^(maxResults|total|startAt)$#',
			'#^issues/(\d+)/key$#',
			'#^issues/(\d+)/fields/worklog/(maxResults|total|startAt)$#',
			'#^issues/(\d+)/fields/worklog/worklogs/(\d+)/(timeSpentSeconds|comment|author/name)$#',
		];
	}

}

header('Content-type: text/plain');

/**
$json = json_decode(file_get_contents('jira.json'));
echo "\n" . number_format(memory_get_peak_usage() / 1e6) . " MB\n";
exit;
/**/

$stream = fopen('jira.json', 'r');

$listener = new SaveAllPathListener;

$_time = microtime(1);

$parser = new Parser($stream, $listener);
$parser->parse();

$_time = microtime(1) - $_time;

print_r($listener->getValue());

echo "\n" . number_format(memory_get_peak_usage() / 1e6) . " MB\n";
echo "\n" . number_format($_time * 1000, 1) . " ms\n";
