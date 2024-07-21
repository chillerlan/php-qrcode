<?php
/**
 * ECICharsetTest.php
 *
 * @created      13.03.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Common;

use chillerlan\QRCode\QRCodeException;
use chillerlan\QRCode\Common\ECICharset;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ECICharsetTest extends TestCase{

	/**
	 * @phpstan-return array<int, array{0: int}>
	 */
	public static function invalidIdProvider():array{
		return [[-1], [1000000]];
	}

	#[DataProvider('invalidIdProvider')]
	public function testInvalidDataException(int $id):void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid charset id:');

		new ECICharset($id);
	}

	/**
	 * @phpstan-return array<int, array{0: int, 1: (string|null)}>
	 */
	public static function encodingProvider():array{
		$params = [];

		foreach(ECICharset::MB_ENCODINGS as $id => $name){
			$params[] = [$id, $name];
		}

		return $params;
	}

	#[DataProvider('encodingProvider')]
	public function testGetName(int $id, string|null $name = null):void{
		$eciCharset = new ECICharset($id);

		$this::assertSame($id, $eciCharset->getID());
		$this::assertSame($name, $eciCharset->getName());
	}

}
