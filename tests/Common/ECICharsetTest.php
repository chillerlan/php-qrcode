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
use PHPUnit\Framework\Attributes\{DataProvider, Test, TestWith};
use PHPUnit\Framework\TestCase;

final class ECICharsetTest extends TestCase{

	#[Test]
	#[TestWith([-1])]
	#[TestWith([1000000])]
	public function invalidDataException(int $id):void{
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

	#[Test]
	#[DataProvider('encodingProvider')]
	public function getName(int $id, string|null $name = null):void{
		$eciCharset = new ECICharset($id);

		$this::assertSame($id, $eciCharset->getID());
		$this::assertSame($name, $eciCharset->getName());
	}

}
