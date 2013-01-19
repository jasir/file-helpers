<?php
namespace jasir\FileHelpers;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/FileHelpers.php';

class FileHelpersTest extends \PHPUnit_Framework_TestCase {

	private $file;

	public function setUp() {
		$this->file = __DIR__ . '/testfile.txt';
	}

	public function tearDown() {
		if(file_exists($this->file)) {
			unlink($this->file);
		}
	}

	function testReadLastLines() {
		$this->createFile(1000);
		$lines = FileHelpers::readLastLines($this->file, 2);
		$this->assertEquals(array("999\n", "1000\n"), $lines);
	}

	function testReadLastLines_no_lines_result() {
		$this->createFile(10);
		$lines = FileHelpers::readLastLines($this->file, 0);
		$this->assertEquals(array(), $lines);
	}

	function testSeekLineBack() {
		$this->createFile(100);

		$fh = fopen($this->file, 'r');
		for ($i = 1; $i <= 50; $i++) {
			$l = fgets($fh);
		}
		$position = FileHelpers::seekLineBack($fh, 5);
		$this->assertEquals(126, $position);
		$line = fgets($fh);
		$this->assertEquals("46\n", $line);
		fclose($fh);
	}

	private function createFile($n) {
		if(file_exists($this->file)) {
			unlink($this->file);
		}
		$fh = fopen($this->file, "w");
		for ($i = 1; $i <= $n; $i++) {
			fputs($fh, "$i\n");
		}
		fclose($fh);
	}
}