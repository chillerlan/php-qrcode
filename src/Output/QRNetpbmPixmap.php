<?php
/**
 * Class QRNetpbmPixmap
 *
 * @created      19.12.2025
 * @author       wgevaert
 * @copyright    2025 wgevaert
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use function pack;
use function str_repeat;

class QRNetpbmPixmap extends QRNetpbmAbstract{

	public const MIME_TYPE = 'image/x-portable-pixmap';

	protected const HEADER_ASCII  = 'P3';
	protected const HEADER_BINARY = 'P6';

	protected function prepareModuleValue(mixed $value):mixed{
		if (!is_array($value) || count($value) !== 3){
			throw new UnexpectedValueException( 'Module value should be array of length 3 for NetpbmPixmap' );
		}
		$newValue = [];
		foreach($value as $rgbValue) {
			$rgbValue = intval( $rgbValue );
			if ( $rgbValue < 0 ) {
				$rgbValue = 0;
			}
			if ( $rgbValue > $this->options->netpbmMaxValue ) {
				$rgbValue = $this->options->netpbmMaxValue;
			}
			$newValue []= $rgbValue;
		}
		return $newValue;
	}

	protected function getDefaultModuleValue(bool $isDark):mixed{
		return array_fill(0, 3, $isDark ? 0 : $this->options->netpbmMaxValue);
	}

	public static function moduleValueIsValid(mixed $value):bool{
		if ( !is_array($value) || count($value) !== 3 ) {
			return false;
		}
		foreach ($value as $rgbVal) {
			if (!is_int($rgbVal) || $rgbVal < 0 || $rgbVal >= 65536) {
				return false;
			}
		}
		return true;
	}

	protected function getBodyASCII():string{
		$body = '';
		$maxLength = (70 - strlen(' '.(string)$this->options->netpbmMaxValue) + 1);

		foreach($this->matrix->getMatrix() as $row){
			$line = '';
			$rowString = '';
			foreach ($row as $module) {
				for ($i = 0; $i < $this->scale; $i++) {
					foreach($this->getModuleValue($module) as $rgbValue) {
						$line .= $rgbValue;
						if (strlen($line) >= $maxLength) {
							$rowString .= $line."\n";
							$line = '';
						} else {
							$line .= ' ';
						}
					}
				}
			}
			$body .= str_repeat(trim($rowString.$line)."\n", $this->scale);
		}

		return $body;
	}

	protected function getBodyBinary():string{
		$format = $this->options->netpbmMaxValue > 255 ? 'n*' : 'C*';
		$body = '';
		foreach ($this->matrix->getMatrix() as $row) {
			$line = '';
			foreach($row as $module) {
				$m = $this->getModuleValue($module);
				$f = pack($format, ...$m);
				$line .= str_repeat(
					$f,
					$this->scale
				);
			}
			$body .= str_repeat($line, $this->scale);
		}
		return $body;
	}
}
