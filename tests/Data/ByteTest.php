<?php
/**
 * Class ByteTest
 *
 * @created      24.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\Byte;
use chillerlan\QRCode\Data\QRDataModeInterface;

/**
 * Tests the Byte class
 */
final class ByteTest extends DataInterfaceTestAbstract{

	protected const testData = '[¯\_(ツ)_/¯]';

	protected static function getDataModeInterface(string $data):QRDataModeInterface{
		return new Byte($data);
	}

	/**
	 * isByte() passses any binary string and only fails on empty strings
	 *
	 * @phpstan-return array<int, array{0: string, 1: bool}>
	 */
	public static function stringValidateProvider():array{
		return [
			["\x01\x02\x03", true],
			['            ', true], // not empty!
			['0', true], // should survive !empty()
			['', false],
		];
	}

	public function testInvalidDataException():void{
		$this::markTestSkipped('N/A (binary mode)');
	}

	public function testBinaryStringInvalid():void{
		$this::markTestSkipped('N/A (binary mode)');
	}

}
