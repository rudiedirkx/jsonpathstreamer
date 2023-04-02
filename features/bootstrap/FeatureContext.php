<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context {

	/** @var string */
	protected $phpBin;
	/** @var Process */
	protected $process;
	/** @var string */
	protected $workingDir;
	/** @var array */
	protected $result;

	/**
	 * Cleans test folders in the temporary directory.
	 *
	 * @BeforeSuite
	 * AfterSuite
	 */
	public static function cleanTestFolders() {
		if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat')) {
			self::clearDirectory($dir);
		}
	}

	/**
	 * Prepares test folders in the temporary directory.
	 *
	 * @BeforeScenario
	 */
	public function prepareTestFolders() {
		$dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat' . DIRECTORY_SEPARATOR . uniqid();

		mkdir($dir . '/features/bootstrap/i18n', 0777, true);
		$this->workingDir = $dir;
		echo $this->workingDir;

		$phpFinder = new PhpExecutableFinder();
		if (false === $php = $phpFinder->find()) {
			throw new \RuntimeException('Unable to find the PHP executable.');
		}

		$this->phpBin = $php;
	}



	/**
	 * @Given I have a JSON file
	 */
	public function iHaveAJsonFile(PyStringNode $string) {
		$this->createFile('data.json', (string) $string);
	}

	/**
	 * @Given I have a listener
	 */
	public function iHaveAListener(PyStringNode $string) {
		$content = array(
			'<?php',
			'',
			'require "' . dirname(dirname(__DIR__)) . '/vendor/autoload.php";',
			'',
			'set_error_handler(function($errno, $error) { throw new ErrorException($error, $errno); });',
			'',
			(string) $string,
			'',
			'$stream = fopen(__DIR__ . "/data.json", "r");',
			'',
			'$listener = new Listener;',
			'',
			'$parser = new \JsonStreamingParser\Parser($stream, $listener);',
			'$parser->parse();',
			'',
			'echo json_encode($listener->getValue(), JSON_PRETTY_PRINT);',
		);
		$this->createFile('data.php', implode("\n", $content));
	}

	/**
	 * @When I run the streamer
	 */
	public function iRunTheStreamer() {
		$this->process = new Process([
			$this->phpBin,
			'data.php',
		]);
		$this->process->setWorkingDirectory($this->workingDir);

		$this->process->start();
		$this->process->wait();

		$output = $this->process->getErrorOutput() . $this->process->getOutput();
		// echo $output;

		$exitCode = $this->process->getExitCode();
		if ($exitCode != 0) {
			throw new Exception('Exit code not 0, but ' . $exitCode . ': ' . $output);
		}

		$this->result = $this->decodeJSON($output);
	}

	/**
	 * @Then I should have
	 */
	public function iShouldHave(PyStringNode $string) {
		$actualData = $this->result;

		$expectedData = $this->decodeJSON($string);

		if ($actualData !== $expectedData) {
			throw new Exception("Output does not match expected result:\n\n" . json_encode($actualData, JSON_PRETTY_PRINT));
		}
	}



	/**
	 *
	 */
	protected function decodeJSON($json) {
		$data = json_decode((string) $json, true);
		if (!is_array($data)) {
			throw new Exception('Output is not JSON parsable: ' . $json);
		}
		return $data;
	}

	/**
	 *
	 */
	protected function createFile($filename, $content) {
		if (!is_dir($this->workingDir)) {
			mkdir($this->workingDir, 0777, true);
		}

		$filepath = $this->workingDir . '/' . $filename;

		$content = trim($content) . "\n\n";

		file_put_contents($filepath, $content);
	}

	/**
	 *
	 */
	protected static function clearDirectory($path) {
		$files = scandir($path);
		array_shift($files);
		array_shift($files);

		foreach ($files as $file) {
			$file = $path . DIRECTORY_SEPARATOR . $file;
			if (is_dir($file)) {
				self::clearDirectory($file);
			} else {
				unlink($file);
			}
		}

		rmdir($path);
	}

}
