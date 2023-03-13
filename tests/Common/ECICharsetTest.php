<?php
/**
 * ECICharsetTest.php
 *
 * @created      13.03.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Common;

use chillerlan\QRCode\Common\ECICharset;
use chillerlan\QRCode\QRCodeException;
use PHPUnit\Framework\TestCase;

final class ECICharsetTest extends TestCase{

	public static function invalidIdProvider():array{
		return [[-1], [1000000]];
	}

	/**
	 * @dataProvider invalidIdProvider
	 */
	public function testInvalidDataException(int $id):void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid charset id:');
		/** @phan-suppress-next-line PhanNoopNew */
		new ECICharset($id);
	}

	public function encodingProvider():array{
		$params = [];

		foreach(ECICharset::MB_ENCODINGS as $id => $name){
			$params[] = [$id, $name];
		}

		return $params;
	}

	/**
	 * @dataProvider encodingProvider
	 */
	public function testGetName(int $id, string $name = null):void{
		$eciCharset = new ECICharset($id);

		$this::assertSame($id, $eciCharset->getID());
		$this::assertSame($name, $eciCharset->getName());
	}

}
