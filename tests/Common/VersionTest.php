<?php
/**
 * Class VersionTest
 *
 * @created      25.07.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Common;

use chillerlan\QRCode\QRCodeException;
use chillerlan\QRCode\Common\{EccLevel, Version};
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Version coverage test
 */
final class VersionTest extends TestCase{

	private Version $version;

	protected function setUp():void{
		$this->version = new Version(7);
	}

	#[Test]
	public function versionToString():void{
		$this::assertSame('7', (string)$this->version);
	}

	#[Test]
	public function getVersionNumber():void{
		$this::assertSame(7, $this->version->getVersionNumber());
	}

	#[Test]
	public function getDimension():void{
		$this::assertSame(45, $this->version->getDimension());
	}

	#[Test]
	public function getVersionPattern():void{
		$this::assertSame(0b000111110010010100, $this->version->getVersionPattern());
		// no pattern for version < 7
		$this::assertNull((new Version(6))->getVersionPattern());
	}

	#[Test]
	public function getAlignmentPattern():void{
		$this::assertSame([6, 22, 38], $this->version->getAlignmentPattern());
	}

	#[Test]
	public function getRSBlocks():void{
		$this::assertSame([18, [[2, 14], [4, 15]]], $this->version->getRSBlocks(new EccLevel(EccLevel::Q)));
	}

	#[Test]
	public function getTotalCodewords():void{
		$this::assertSame(196, $this->version->getTotalCodewords());
	}

	#[Test]
	public function constructInvalidVersion():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid version given');

		new Version(69);
	}

}
