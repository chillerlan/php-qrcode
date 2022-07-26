<?php
/**
 * Class VersionTest
 *
 * @created      25.07.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Common;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\QRCodeException;
use PHPUnit\Framework\TestCase;

/**
 * Version coverage test
 */
final class VersionTest extends TestCase{

	private Version $version;

	protected function setUp():void{
		$this->version = new Version(7);
	}

	public function testToString():void{
		$this::assertSame('7', (string)$this->version);
	}

	public function testGetVersionNumber():void{
		$this::assertSame(7, $this->version->getVersionNumber());
	}

	public function testGetDimension():void{
		$this::assertSame(45, $this->version->getDimension());
	}

	public function testGetVersionPattern():void{
		$this::assertSame(0b000111110010010100, $this->version->getVersionPattern());
		// no pattern for version < 7
		$this::assertNull((new Version(6))->getVersionPattern());
	}

	public function testGetAlignmentPattern():void{
		$this::assertSame([6, 22, 38], $this->version->getAlignmentPattern());
	}

	public function testGetRSBlocks():void{
		$this::assertSame([18, [[2, 14], [4, 15]]], $this->version->getRSBlocks(new EccLevel(EccLevel::Q)));
	}

	public function testGetTotalCodewords():void{
		$this::assertSame(196, $this->version->getTotalCodewords());
	}

	public function testConstructInvalidVersion():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid version given');

		$version = new Version(69);
	}

}
