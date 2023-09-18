<?php
/**
 * Class KanjiTest
 *
 * @created      24.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\Kanji;
use Generator, Throwable;
use function bin2hex, chr, defined, sprintf;

/**
 * Tests the Kanji class
 */
final class KanjiTest extends DataInterfaceTestAbstract{

	protected static string $FQN      = Kanji::class;
	protected static string $testdata = '漂う花の香り';

	/**
	 * isKanji() should pass on Kanji/SJIS characters and fail on everything else
	 */
	public static function stringValidateProvider():array{
		return [
			['茗荷', true],
			['Ã', false], // this will fail in SJIS-2004
			['ABC', false],
			['123', false],
			['漂う花の香り', true], // https://genshin-impact.fandom.com/wiki/Floral_Incense
			['꽃잎 향초의 기도', false], // same as above in korean
		];
	}

	/**
	 * lists the valid SJIS kanji
	 */
	public static function kanjiProvider():Generator{
		$key = fn(int $byte1, int $byte2):string => sprintf('0x%X', ($byte1 << 8 | $byte2));
		$val = fn(int $byte1, int $byte2):string => mb_convert_encoding(chr($byte1).chr($byte2), 'UTF-8', Kanji::ENCODING);

		for($byte1 = 0x81; $byte1 < 0xeb; $byte1++){

			// skip invalid/vendor ranges
			if(($byte1 > 0x84 && $byte1 < 0x88) || ($byte1 > 0x9f && $byte1 < 0xe0)){
				continue;
			}

			// second byte of a double-byte JIS X 0208 character whose first half of the JIS sequence was odd
			if(($byte1 % 2) !== 0){

				for($byte2 = 0x40; $byte2 < 0x9f; $byte2++){

					if($byte2 === 0x7f){
						continue;
					}

					yield $key($byte1, $byte2) => [$val($byte1, $byte2)];
				}

			}
			// second byte if the first half of the JIS sequence was even
			else{

				for($byte2 = 0x9f; $byte2 < 0xfd; $byte2++){
					yield $key($byte1, $byte2) => [$val($byte1, $byte2)];
				}

			}

		}

	}

	/**
	 * @group slow
	 * @dataProvider kanjiProvider
	 */
	public function testValidateSJIS(string $chr):void{
		// we may run into several issues due to encoding detection failures
		try{
			$this::assertTrue(Kanji::validateString($chr));
		}
		catch(Throwable $e){
			/** @noinspection PhpUndefinedConstantInspection - see phpunit.xml.dist */
			if(defined('TEST_IS_CI') && TEST_IS_CI === true){
				$this::markTestSkipped();
			}

			$this::markTestSkipped(sprintf(
				'invalid glyph: %s => %s',
				bin2hex(mb_convert_encoding($chr, Kanji::ENCODING, 'UTF-8')),
				$chr
			));
		}
	}

}
