<?php
/**
 * Class AlphaNumTest
 *
 * @created      24.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\AlphaNum;
use chillerlan\QRCode\Data\QRDataModeInterface;

/**
 * Tests the AlphaNum class
 */
final class AlphaNumTest extends DataInterfaceTestAbstract{

	protected const testData = '0 $%*+-./:';

	protected static function getDataModeInterface(string $data):QRDataModeInterface{
		return new AlphaNum($data);
	}

	/**
	 * isAlphaNum() should pass on the 45 defined characters and fail on anything else (e.g. lowercase)
	 */
	public static function stringValidateProvider():array{
		return [
			['ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', true],
			['abc', false],
			['ÄÖÜ', false],
			[',', false],
			['-', true],
			['+', true],
			['.', true],
			['*', true],
			[':', true],
			['/', true],
			['\\', false],
		];
	}

}
