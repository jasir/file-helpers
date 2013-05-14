<?php
namespace jasir\FileHelpers;

require_once __DIR__ . '/../vendor/autoload.php';

class DirectoryTest extends \PHPUnit_Framework_TestCase {

    function setup() {
        $this->deleteDirs();
        $this->createDirs();
    }
    
    function tearDown() {
        $this->deleteDirs();
    }

    function test_deleteRecursive() {
        $dir = $this->getPath();
        $this->assertFileExists("$dir/subdir/file.txt");
        Directory::deleteRecursive($dir);
        $this->assertFileNotExists("$dir/subdir/file.txt");
        $this->assertFileNotExists("$dir");
    }

    function test_deleteRecursive_keepsRootdir() {
        $dir = $this->getPath();
        $this->assertFileExists("$dir/subdir/file.txt");
        Directory::deleteRecursive($dir, FALSE);
        $this->assertFileNotExists("$dir/subdir/file.txt");
        $this->assertFileNotExists("$dir/subdir");
        $this->assertFileExists("$dir");
    }

    
    private function deleteDirs() {
        $dir = $this->getPath();
        @unlink("$dir/subdir/file.txt");
        @rmdir("$dir/subdir");
        @rmdir("$dir");
        
        
    }
    
    private function createDirs() {
        $dir = $this->getPath();
        mkdir($dir);
        mkdir("$dir/subdir");
        file_put_contents("$dir/subdir/file.txt", "hello");
    }
    
    private function getPath() {
        return __DIR__ . '/delete';
    }

}