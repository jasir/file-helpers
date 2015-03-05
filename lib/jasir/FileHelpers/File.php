<?php

namespace jasir\FileHelpers;

use InvalidArgumentException;

class File {

	/**
	 * Return array with last n lines from given file
	 * @param string|resource file
	 * @param int $lines
	 * @return array
	 */
	public static function readLastLines($file, $lines)
	{

		if (is_string($file)) {
			if (!file_exists($file)) {
				return FALSE;
			}
			$fh = fopen($file, "r");
			if ($fh === FALSE) {
				return FALSE;
			}
		} else {
			$fh = $file;
		}

		fseek($fh, 0, SEEK_END);

		$position = static::seekLineBack($fh, $lines);
		$lines = array();

		while ($line = fgets($fh)) {
			$lines[] = $line;
		}

		if (is_string($file)) {
			fclose($fh);
		}

		return $lines;
	}


	/**
	 * Will set pointer in file back to read n lines relative to current pointer
	 *
	 * @param resource $fh
	 * @param int number of lines
	 * @return int
	 * @see http://stackoverflow.com/questions/2961618/how-to-read-only-5-last-line-of-the-txt-file
	 */
	public static function seekLineBack($fh, $n)
	{

		$readSize = 160 * ($n + 1);
		$pos = ftell($fh);

		if (ftell($fh) === 0) {
			return FALSE;
		}


		if($pos === FALSE) {
			fseek($fh, 0, SEEK_SET);
			return FALSE;
		}

		while ($n >= 0) {
			if ($pos === 0) {
				break;
			}

			$currentReadsize = $readSize;
			$pos = $pos - $readSize;
			if ($pos < 0) {
				$currentReadsize = $readSize - abs($pos);
				$pos = 0;
			}

			if (fseek($fh, $pos, SEEK_SET) === -1) {
				fseek($fh, 0, SEEK_SET);
				break;
			}

			$data = fread($fh, $currentReadsize);
			$count = substr_count($data, "\n");
			$n = $n - $count;

			if ($n < 0) {
				break;
			}

		}

		fseek($fh, $pos, SEEK_SET);

		while ($n < 0) {
			fgets($fh);
			$n++;
		}

		$pos = ftell($fh);
		if ($pos === FALSE) {
			fseek($fh, 0, SEEK_SET);
		}
		return $pos;
	}

	/**
	 * Saves content to file, autocreates path to file
	 *
	 * @param mixed $path
	 * @param mixed $content
	 */
	public static function saveFile($path, $content)
	{
		$path = self::normalizePath($path);
		$dir = dirname($path);
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
		file_put_contents($path, $content);
	}


	/**
	 * Converts path to be relative to given $relativeTo path
	 *
	 * @param string $path
	 * @param string $relativeTo
	 * @return string
	 */
	public static function getRelative($path, $relativeTo)
	{
		//absolutize and unixize paths
		$path = self::simplifyPath($path);
		$relativeTo = self::simplifyPath($relativeTo);

		// clean paths by removing trailing and prefixing slashes
		$path = trim($path, '/');
		$relativeTo = trim($relativeTo, '/');

		// simple case: $compareTo is in $path
		if (strpos($path, $relativeTo) === 0) {
			return substr($path, strlen($relativeTo) + 1);
		}

		$relative       = array();
		$pathParts      = explode('/', $path);
		$compareToParts = explode('/', $relativeTo);

		foreach ($compareToParts as $index => $part) {
			if (isset($pathParts[$index]) && $pathParts[$index] == $part) {
				continue;
			}
			$relative[] = '..';
		}

		foreach ($pathParts as $index => $part) {
			if (isset($compareToParts[$index]) && $compareToParts[$index] == $part) {
				continue;
			}
			$relative[] = $part;
		}
		return implode('/', $relative);
	}


	/**
	 * Converts path to be absolute
	 *
	 * @param string $path
	 * @return string
	 */
	public static function simplifyPath($path)
	{
		  $path = self::normalizeSlashes($path);
		  $parts = array_filter(explode('/', $path), 'strlen');
		  $absolutes = array();
		  foreach ($parts as $part) {
				if ('.' == $part) {
					continue;
				}
				if ('..' == $part) {
					 array_pop($absolutes);
				} else {
					 $absolutes[] = $part;
				}
		  }
		  return implode('/', $absolutes);
	}


	/**
	 * Normalize path
	 *
	 * @param   string  $path
	 * @param   string  $separator
	 * @return  string  normalized path
	 * @throws  InvalidArgumentException
	 */
	public static function normalizePath($path, $separator = '/')
	{
		// Remove any kind of funky unicode whitespace
		$normalized = preg_replace('#\p{C}+|^\./#u', '', $path);

		// Path remove self referring paths ("/./").
		$normalized = preg_replace('#/\.(?=/)|^\./|\./$#', '', $normalized);

		// Regex for resolving relative paths
		$regex = '#\/*[^/\.]+/\.\.#Uu';

		while (preg_match($regex, $normalized)) {
			$normalized = preg_replace($regex, '', $normalized);
		}

		if (preg_match('#/\.{2}|\.{2}/#', $normalized)) {
			throw new InvalidArgumentException('Path is outside of the defined root, path: [' . $path . '], resolved: [' . $normalized . ']');
		}

		if ($separator !== '/') {
			$normalized = str_replace("/", "\\", $normalized);
		}


		return trim($normalized, $separator);
	}


	static function normalizeSlashes($path)
	{
		return str_replace('\\', '/', $path);
	}


	static function normalizeWindows($path)
	{
		return str_replace('/', '\\', $path);
	}



	 /**
	  * Converts file path to unix standards
	  * @param string $path
	  */
	public static function unixisePath($path)
	{
		return str_replace(array(':', '\\'), array('', '/'), $path);
	}



}