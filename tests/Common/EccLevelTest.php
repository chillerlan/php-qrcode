<?php
/**
 * Class EccLevelTest
 *
 * @created      25.07.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Common;

use chillerlan\QRCode\QRCodeException;
use chillerlan\QRCode\Common\{EccLevel, MaskPattern};
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * EccLevel coverage test
 */
final class EccLevelTest extends TestCase{

	#[Test]
	public function constructInvalidEccException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid ECC level');

		new EccLevel(69);
	}

	#[Test]
	public function eccToString():void{
		$ecc = new EccLevel(EccLevel::L);

		$this::assertSame('L', (string)$ecc);
	}

	#[Test]
	public function getLevel():void{
		$ecc = new EccLevel(EccLevel::L);

		$this::assertSame(EccLevel::L, $ecc->getLevel());
	}

	#[Test]
	public function getOrdinal():void{
		$ecc = new EccLevel(EccLevel::L);

		$this::assertSame(0, $ecc->getOrdinal());
	}

	#[Test]
	public function getformatPattern():void{
		$ecc = new EccLevel(EccLevel::Q);

		$this::assertSame(0b010010010110100, $ecc->getformatPattern(new MaskPattern(4)));
	}

	#[Test]
	public function getMaxBits():void{
		$ecc = new EccLevel(EccLevel::Q);

		$this::assertSame(4096, $ecc->getMaxBits()[21]);
	}

}
