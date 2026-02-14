<?php
/**
 * Class QRNetbmBitmap
 *
 * @created      19.12.2025
 * @author       wgevaert & codemasher
 * @copyright    2025 wgevaert & codemasher
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use UnexpectedValueException;
use function is_bool;
use function str_split;
use function pack;
use function str_repeat;

class QRNetpbmBitmap extends QRNetpbmAbstract{

	public const MIME_TYPE = 'image/x-portable-bitmap';

	protected const HEADER_ASCII  = 'P1';
	protected const HEADER_BINARY = 'P4';

	protected function prepareModuleValue(mixed $value):mixed{
		if ( !is_bool( $value ) ) {
			throw new UnexpectedValueException( 'Bitmap expected bool modules' );
		}
		return $value;
	}

	protected function getDefaultModuleValue(bool $isDark):mixed{
		return $isDark;
	}

	public static function moduleValueIsValid(mixed $value):bool{
		return is_bool($value);
	}

	protected function setModuleValues():void{
		// noop
	}

	protected function getMaxValueHeaderString(): string {
		return '';
	}

	protected function getBodyASCII():string{
		$body = '';

		foreach($this->matrix->getBooleanMatrix() as $row){
			$line = '';

			foreach($row as $isDark){
				$line .= str_repeat($isDark ? '1' : '0', $this->scale);
			}
			// Lines should not be longer than 70 chars
			$line = implode("\n", str_split($line,70))."\n";

			$body .= str_repeat($line, $this->scale);
		}

		return $body;
	}

	protected function getBodyBinary():string{
		$body = '';

		foreach($this->matrix->getBooleanMatrix() as $row){
			$rowdata = array_fill(0, (int)ceil($this->length / 8), 0);
			$byte    = 0;
			$bit     = 0b10000000;

			foreach($row as $isDark){
				for($i = 0; $i < $this->scale; $i++){
					if($bit <= 0){
						$bit = 0b10000000;
						$byte++;
					}
					if($isDark){
						$rowdata[$byte] |= $bit;
					}
					$bit >>= 1;
				}
			}

			$rowdataString = pack('C*', ...$rowdata);

			$body .= str_repeat($rowdataString, $this->scale);
		}
		return $body;
	}
}
