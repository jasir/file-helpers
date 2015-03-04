<?php
namespace jasir\FileHelpers;

require_once __DIR__ . '/../vendor/autoload.php';

class FileTest extends \PHPUnit_Framework_TestCase {

	private $file;


	public function setUp()
	{
		$this->file = __DIR__ . '/testfile.txt';
	}


	public function tearDown()
	{
		if(file_exists($this->file)) {
			unlink($this->file);
		}
	}


	function testReadLastLines()
	{
		$this->createFile(1000);
		$lines = File::readLastLines($this->file, 2);
		$this->assertEquals(array("999\n", "1000\n"), $lines);
	}


	function testReadLastLines_no_lines_result()
	{
		$this->createFile(10);
		$lines = File::readLastLines($this->file, 0);
		$this->assertEquals(array(), $lines);
	}


	function testSeekLineBack()
	{
		$this->createFile(100);

		$fh = fopen($this->file, 'r');
		for ($i = 1; $i <= 50; $i++) {
			$l = fgets($fh);
		}
		$position = File::seekLineBack($fh, 5);
		$this->assertEquals(126, $position);
		$line = fgets($fh);
		$this->assertEquals("46\n", $line);
		fclose($fh);
	}


	private function createFile($n)
	{
		if(file_exists($this->file)) {
			unlink($this->file);
		}
		$fh = fopen($this->file, "w");
		for ($i = 1; $i <= $n; $i++) {
			fputs($fh, "$i\n");
		}
		fclose($fh);
	}


	public static function getRelativePathProvider()
	{
		//  [path, relativeto, expectedRelativePath]

		return [
			['/srv/foo/bar', '/srv', 'foo/bar'],
			['/srv/foo/bar', '/srv/', 'foo/bar'],
			['/srv/foo/bar/', '/srv', 'foo/bar'],
			['/srv/foo/bar/', '/srv/', 'foo/bar'],
			['/srv/foo/bar', '/srv/test', '../foo/bar'],
			['/srv/foo/bar', '/srv/test/fool', '../../foo/bar'],
			['/srv/mad/xp/mad/model/static/css/uni-form.css', '/srv/mad/xp/liria/', '../mad/model/static/css/uni-form.css'],
			['c:/work/here', 'c:/work/from', '../here'],
			['c:\work\here', 'c:\work\from', '../here'],
			['/here', '/homes/jasir', '../../here'],
		];
	}


	public function testSimplifyPath()
	{
		$this->assertEquals("this/a/test/is", File::simplifyPath('this/is/../a/./test/.///is'));
	}


	public function testUnixisePath()
	{
		$this->assertEquals("c/work/shit", File::unixisePath('c:/work\\shit'));
	}

	/**
	 * @dataProvider getRelativePathProvider
	 */
	public function testGetRelativePath($path, $compareTo, $expected)
	{
		$result = File::getRelative( $path, $compareTo );
		$this->assertEquals( $expected, $result );
	}

	public function testNormalizePath()
	{
		$this->assertEquals("aaa/b/c", File::normalizePath('aaa/xxx/../b/c'));
	}


}