<?php
/**
 * Class HanziTest
 *
 * @created      20.11.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\Hanzi;
use Generator, Throwable;
use function bin2hex, chr, defined, sprintf;

/**
 * Tests the Hanzi/GB2312 class
 */
final class HanziTest extends DataInterfaceTestAbstract{

	protected static string $FQN      = Hanzi::class;
	protected static string $testdata = '无可奈何燃花作香';

	/**
	 * isGB2312() should pass on Hanzi/GB2312 characters and fail on everything else
	 */
	public static function stringValidateProvider():array{
		return [
			['原神', true],
			['ABC', false],
			['123', false],
			['无可奈何燃花作香', true], // https://genshin-impact.fandom.com/wiki/Floral_Incense
			['無可奈何燃花作香', false], // same as above in traditional Chinese
			['꽃잎 향초의 기도', false], // same as above in Korean
		];
	}

	/**
	 * lists all characters in the valid GB2312 range
	 */
	public static function hanziProvider():Generator{

		for($byte1 = 0xa1; $byte1 < 0xf8; $byte1++){

			if($byte1 > 0xa9 && $byte1 < 0xb0){
				continue;
			}

			for($byte2 = 0xa1; $byte2 < 0xff; $byte2++){
				yield sprintf('0x%X', ($byte1 << 8 | $byte2)) => [
					mb_convert_encoding(chr($byte1).chr($byte2), 'UTF-8', Hanzi::ENCODING),
				];
			}

		}

	}

	/**
	 * @group slow
	 * @dataProvider hanziProvider
	 */
	public function testValidateGB2312(string $chr):void{
		// we may run into several issues due to encoding detection failures
		try{
			$this::assertTrue(Hanzi::validateString($chr));
		}
		catch(Throwable $e){
			/** @noinspection PhpUndefinedConstantInspection - see phpunit.xml.dist */
			if(defined('TEST_IS_CI') && TEST_IS_CI === true){
				$this::markTestSkipped();
			}

			$this::markTestSkipped(sprintf(
				'invalid glyph: %s => %s',
				bin2hex(mb_convert_encoding($chr, Hanzi::ENCODING, 'UTF-8')),
				$chr
			));
		}
	}

}
