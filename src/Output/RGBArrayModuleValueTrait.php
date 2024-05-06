<?php
/**
 * RGBArrayModuleValueTrait.php
 *
 * @created      06.05.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use function array_values, count, intval, is_array, is_numeric, max, min;

/**
 * Module value checks for output classes that use RGB color arrays
 */
trait RGBArrayModuleValueTrait{

	/**
	 * @implements \chillerlan\QRCode\Output\QROutputInterface::moduleValueIsValid()
	 * @inheritDoc
	 */
	public static function moduleValueIsValid(mixed $value):bool{

		if(!is_array($value) || count($value) < 3){
			return false;
		}

		// check the first 3 values of the array
		foreach(array_values($value) as $i => $val){

			if($i > 2){
				break;
			}

			if(!is_numeric($val)){
				return false;
			}

		}

		return true;
	}

	/**
	 * @implements \chillerlan\QRCode\Output\QROutputAbstract::prepareModuleValue()
	 * @inheritDoc
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function prepareModuleValue(mixed $value):array{
		$values = [];

		foreach(array_values($value) as $i => $val){

			if($i > 2){
				break;
			}

			$values[] = max(0, min(255, intval($val)));
		}

		if(count($values) !== 3){
			throw new QRCodeOutputException('invalid color value');
		}

		return $values;
	}

	/**
	 * @implements \chillerlan\QRCode\Output\QROutputAbstract::getDefaultModuleValue()
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):array{
		return ($isDark) ? [0, 0, 0] : [255, 255, 255];
	}

}
