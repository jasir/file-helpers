<?php

namespace jasir\FileHelpers;

class File {

	/**
	 * Return array with last n lines from given file
	 * @param string|resource file
	 * @param int $lines
	 * @return array
	 */
	public static function readLastLines($file, $lines) {

		if (is_string($file)) {
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
	public static function seekLineBack($fh, $n) {

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

	static function normalizeSlashes($path) {
		return str_replace('\\', '/', $path);
	}


}