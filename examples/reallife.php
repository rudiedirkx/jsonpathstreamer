<?php

use JsonStreamingParser\Parser;
use rdx\jsonpathstreamer\RegexTargetConfigJsonListener;

require __DIR__ . '/../vendor/autoload.php';

class SaveAllPathListener extends RegexTargetConfigJsonListener {

	protected $first  = 0;
	protected $last = [];

	protected $count  = 0;

	public function __construct() {
		parent::__construct();

		$this->first = microtime(1);
		$this->last = [microtime(1)];
	}

	public function getRules() {
		return [
			'#^packages/(\d+)/name$#' => '$1',
			'#^features/(\d+)/properties/STREET$#' => '$1',
		];
	}

	protected function rememberValue( array $path, $value ) {
		parent::rememberValue($path, $value);

		$this->count++;
		$now = microtime(1);

		$last_count = count($this->last);
		$last_time = $this->last[0];

		printf(
			"saved % 6d (%4.1f MB) - %6.1f/sec - %6.1f/sec\n",
			$this->count,
			memory_get_peak_usage()/1e6,
			$this->count / ($now - $this->first),
			$last_count / ($now - $last_time)
		);

		array_push($this->last, $now);
		if ($last_count > 200) {
			array_shift($this->last);
		}

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

print_r(array_slice($listener->getValue(), 0, 1000));

echo "\n" . number_format($_time * 1000, 1) . " ms\n";
