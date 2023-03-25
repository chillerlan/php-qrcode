<?php
/**
 * Class ByteTest
 *
 * @created      24.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\Byte;

/**
 * Tests the Byte class
 */
final class ByteTest extends DataInterfaceTestAbstract{

	protected static string $FQN      = Byte::class;
	protected static string $testdata = '[¯\_(ツ)_/¯]';

	/**
	 * isByte() passses any binary string and only fails on empty strings
	 */
	public static function stringValidateProvider():array{
		return [
			["\x01\x02\x03", true],
			['            ', true], // not empty!
			['0', true], // should survive !empty()
			['', false],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function testInvalidDataException():void{
		/** @noinspection PhpUnitTestFailedLineInspection */
		$this::markTestSkipped('N/A (binary mode)');
	}

	/**
	 * @inheritDoc
	 */
	public function testBinaryStringInvalid():void{
		/** @noinspection PhpUnitTestFailedLineInspection */
		$this::markTestSkipped('N/A (binary mode)');
	}

}
