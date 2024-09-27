<?php
/**
 * Class NumberTest
 *
 * @created      24.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\Number;
use chillerlan\QRCode\Data\QRDataModeInterface;

/**
 * Tests the Number class
 */
final class NumberTest extends DataInterfaceTestAbstract{

	protected const testData = '0123456789';

	protected static function getDataModeInterface(string $data):QRDataModeInterface{
		return new Number($data);
	}

	/**
	 * isNumber() should pass on any number and fail on anything else
	 *
	 * @phpstan-return array<int, array{0: string, 1: bool}>
	 */
	public static function stringValidateProvider():array{
		return [
			['0123456789', true],
			['ABC123', false],
		];
	}

}
