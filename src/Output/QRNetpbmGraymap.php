<?php
/**
 * Class QRPbm
 *
 * @created      19.12.2025
 * @author       wgevaert
 * @copyright    2025 wgevaert
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Output\QROutputAbstract;
use UnexpectedValueException;
use function is_bool;
use function str_pad;
use function str_split;
use function pack;
use function bindec;
use function str_repeat;

class QRNetpbmGraymap extends QRNetpbmAbstract{

	public const MIME_TYPE = 'image/x-portable-graymap';

	protected const HEADER_ASCII  = 'P2';
	protected const HEADER_BINARY = 'P5';

	protected function prepareModuleValue(mixed $value):mixed{
		$value = intval( $value );
		if ( $value < 0 )
			return 0;
		if ( $value > $this->options->netpbmMaxValue )
			return $this->options->netpbmMaxValue;
		return $value; 
	}

	protected function getDefaultModuleValue(bool $isDark):mixed{
		return $isDark ? 0 : $this->options->netpbmMaxValue;
	}

	public static function moduleValueIsValid(mixed $value):bool{
		// Since this is called statically, we cannot know what $this->options->netpbmMaxValue will be.
		return is_int($value) && 0<$value && $value<65536;
	}

	protected function getBodyASCII():string{
		$body = '';
		$maxLength = 70 - strlen(' ' . (string)$this->options->netpbmMaxValue) + 1;

                foreach($this->matrix->getMatrix() as $row){
			$line = '';
			$rowString = '';
			foreach ($row as $module) {
				for ($i=0;$i<$this->scale;$i++) {
					$line .= $this->getModuleValue($module);
					if (strlen($line) >= $maxLength) {
						$rowString .= $line . "\n";
						$line = '';
					} else {
						$line .= ' ';
					}
				}
			}
                        $body .= str_repeat(trim($rowString . $line)."\n", $this->scale);
                }

		return $body;
	}

	protected function getBodyBinary():string{
		$body = '';
		foreach ($this->matrix->getMatrix() as $row) {
			$line = '';
			foreach($row as $module) {
				$line .= str_repeat($this->pack($this->getModuleValue($module)), $this->scale);
			}
			$body .= str_repeat($line, $this->scale);
		}
		return $body;
	}
	protected function pack( int $moduleValue ) {
		if ( $this->options->netpbmMaxValue > 255 ) {
			return pack('n', $moduleValue);
		}
		return pack('C', $moduleValue);
	}
}
