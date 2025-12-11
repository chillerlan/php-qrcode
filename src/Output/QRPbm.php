<?php
/**
 * Class QRPbm
 *
 * @created      11.12.2025
 * @author       wgevaert
 * @copyright    2025 wgevaert
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Output\QROutputAbstract;

class QRPbm extends QROutputAbstract {
	private const LIGHT_DARK = [ '0', '1' ];
	protected function prepareModuleValue(mixed $value):mixed{
		return $value;
	}
	protected function getDefaultModuleValue(bool $isDark):mixed{
		return $isDark ? self::LIGHT_DARK[1] : self::LIGHT_DARK[0];
	}
	public static function moduleValueIsValid(mixed $value):bool{
		return is_string($value) && in_array( $value, self::LIGHT_DARK, true );
	}
	public function dump(string|null $file = null):mixed {
		$size = $this->matrix->getSize();
		$result = "P1\n"
                    .$size.' '.$size."\n";
		foreach($this->matrix->getBooleanMatrix() as $row) {
			foreach ($row as $isDark) {
				$result .= $this->getDefaultModuleValue( $isDark );
			}
			$result .= "\n";
		}
		if ( is_string($file) ) {
			file_put_contents($file,$result);
		}
		return $result;
	}
}
