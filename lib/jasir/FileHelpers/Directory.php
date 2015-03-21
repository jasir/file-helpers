<?php

namespace jasir\FileHelpers;

class Directory {

	/**
	 * @param string $directory
	 * @param bool $deleteDir
	 * @return success
	 * implementation from http://lixlpixel.org/recursive_function/php/recursive_directory_delete/
	 */

	static function deleteRecursive($directory, $deleteDir = TRUE) {
		$directory = str_replace('\\', '/', $directory);
		if (substr($directory, -1) == '/') {
			$directory = substr($directory, 0, -1);
		}
		if (!file_exists($directory) || !is_dir($directory)) {
			return FALSE;
		} elseif (is_readable($directory)) {
			$handle = opendir($directory);
			while (FALSE !== ($item = readdir($handle))) {
				if ($item !== '.' && $item !== '..') {
					$path = $directory . '/' . $item;
					if (is_dir($path)) {
						self::deleteRecursive($path);
					} else {
						unlink($path);
					}
				}
			}

			closedir($handle);
			if ($deleteDir === TRUE) {
				if (!rmdir($directory)) {
					return FALSE;
				}
			}
		}
		return TRUE;
	}
}