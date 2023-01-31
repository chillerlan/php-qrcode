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
use Throwable;
use function bin2hex;
use function defined;
use function sprintf;

/**
 * Tests the Hanzi/GB2312 class
 */
class HanziTest extends DataInterfaceTestAbstract{

	protected string $FQN      = Hanzi::class;
	protected string $testdata = '无可奈何燃花作香';

	/**
	 * isGB2312() should pass on Hanzi/Hanzi characters and fail on everything else
	 */
	public function stringValidateProvider():array{
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
	public function hanziProvider():array{
		$list = [];

		for($byte1 = 0xa1; $byte1 < 0xf8; $byte1 += 0x1){

			if($byte1 > 0xa9 && $byte1 < 0xb0){
				continue;
			}

			for($byte2 = 0xa1; $byte2 < 0xff; $byte2++){
				$list[] = [chr($byte1).chr($byte2)];
			}

		}

		return array_map(fn($chr) => mb_convert_encoding($chr, 'UTF-8', Hanzi::ENCODING), $list);
	}

	/**
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
				bin2hex($chr),
				mb_convert_encoding($chr, Hanzi::ENCODING, mb_internal_encoding())
			));
		}
	}

}
