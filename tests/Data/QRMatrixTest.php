<?php
/**
 * Class QRMatrixTest
 *
 * @created      17.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Common\{EccLevel, MaskPattern, Version};
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\{QRCodeDataException, QRMatrix};
use PHPUnit\Framework\TestCase;
use Generator;

/**
 * Tests the QRMatix class
 */
final class QRMatrixTest extends TestCase{

	private const version = 40;
	private QRMatrix $matrix;

	/**
	 * invokes a QRMatrix object
	 */
	protected function setUp():void{
		$this->matrix = $this->getMatrix($this::version);
	}

	/**
	 * shortcut
	 */
	protected function getMatrix(int $version):QRMatrix{
		return  new QRMatrix(new Version($version), new EccLevel(EccLevel::L));
	}

	/**
	 * Validates the QRMatrix instance
	 */
	public function testInstance():void{
		$this::assertInstanceOf(QRMatrix::class, $this->matrix);
	}

	/**
	 * Tests if size() returns the actual matrix size/count
	 */
	public function testSize():void{
		$this::assertCount($this->matrix->size(), $this->matrix->matrix());
	}

	/**
	 * Tests if version() returns the current (given) version
	 */
	public function testVersion():void{
		$this::assertSame($this::version, $this->matrix->version()->getVersionNumber());
	}

	/**
	 * Tests if eccLevel() returns the current (given) ECC level
	 */
	public function testECC():void{
		$this::assertSame(EccLevel::L, $this->matrix->eccLevel()->getLevel());
	}

	/**
	 * Tests if maskPattern() returns the current (or default) mask pattern
	 */
	public function testMaskPattern():void{
		$this::assertSame(null, $this->matrix->maskPattern());

		$matrix = (new QRCode)->addByteSegment('testdata')->getMatrix();

		$this::assertInstanceOf(MaskPattern::class, $matrix->maskPattern());
		$this::assertSame(MaskPattern::PATTERN_100, $matrix->maskPattern()->getPattern());
	}

	/**
	 * Tests the set(), get() and check() methods
	 */
	public function testGetSetCheck():void{
		$this->matrix->set(10, 10, true, QRMatrix::M_TEST);
		$this::assertSame(QRMatrix::M_TEST | QRMatrix::IS_DARK, $this->matrix->get(10, 10));
		$this::assertTrue($this->matrix->check(10, 10));

		$this->matrix->set(20, 20, false, QRMatrix::M_TEST);
		$this::assertSame(QRMatrix::M_TEST, $this->matrix->get(20, 20));
		$this::assertFalse($this->matrix->check(20, 20));
	}

	/**
	 * Version data provider for several pattern tests
	 */
	public function versionProvider():Generator{
		foreach(range(1, 40) as $i){
			yield 'version: '.$i => [$i];
		}
	}

	/**
	 * Tests setting the dark module and verifies its position
	 *
	 * @dataProvider versionProvider
	 */
	public function testSetDarkModule(int $version):void{
		$matrix = $this->getMatrix($version)->setDarkModule();

		$this::assertSame(QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK, $matrix->get(8, $matrix->size() - 8));
	}

	/**
	 * Tests setting the finder patterns and verifies their positions
	 *
	 * @dataProvider versionProvider
	 */
	public function testSetFinderPattern(int $version):void{
		$matrix = $this->getMatrix($version)->setFinderPattern();

		$this::assertSame(QRMatrix::M_FINDER | QRMatrix::IS_DARK, $matrix->get(0, 0));
		$this::assertSame(QRMatrix::M_FINDER | QRMatrix::IS_DARK, $matrix->get(0, $matrix->size() - 1));
		$this::assertSame(QRMatrix::M_FINDER | QRMatrix::IS_DARK, $matrix->get($matrix->size() - 1, 0));
	}

	/**
	 * Tests the separator patterns and verifies their positions
	 *
	 * @dataProvider versionProvider
	 */
	public function testSetSeparators(int $version):void{
		$matrix = $this->getMatrix($version)->setSeparators();

		$this::assertSame(QRMatrix::M_SEPARATOR, $matrix->get(7, 0));
		$this::assertSame(QRMatrix::M_SEPARATOR, $matrix->get(0, 7));
		$this::assertSame(QRMatrix::M_SEPARATOR, $matrix->get(0, $matrix->size() - 8));
		$this::assertSame(QRMatrix::M_SEPARATOR, $matrix->get($matrix->size() - 8, 0));
	}

	/**
	 * Tests the alignment patterns and verifies their positions - version 1 (no pattern) skipped
	 *
	 * @dataProvider versionProvider
	 */
	public function testSetAlignmentPattern(int $version):void{

		if($version === 1){
			$this::markTestSkipped('N/A (Version 1 has no alignment pattern)');
		}

		$matrix = $this
			->getMatrix($version)
			->setFinderPattern()
			->setAlignmentPattern()
		;

		$alignmentPattern = (new Version($version))->getAlignmentPattern();

		foreach($alignmentPattern as $py){
			foreach($alignmentPattern as $px){

				if($matrix->checkType($px, $py, QRMatrix::M_FINDER)){
					// skip finder pattern
					continue;
				}

				$this::assertSame(QRMatrix::M_ALIGNMENT | QRMatrix::IS_DARK, $matrix->get($px, $py));
			}
		}

	}

	/**
	 * Tests the timing patterns and verifies their positions
	 *
	 * @dataProvider versionProvider
	 */
	public function testSetTimingPattern(int $version):void{

		$matrix = $this
			->getMatrix($version)
			->setAlignmentPattern()
			->setTimingPattern()
		;

		$size = $matrix->size();

		for($i = 7; $i < $size - 7; $i++){
			if($i % 2 === 0){

				if($matrix->checkType(6, $i, QRMatrix::M_ALIGNMENT)){
					// skip alignment pattern
					continue;
				}

				$this::assertSame(QRMatrix::M_TIMING | QRMatrix::IS_DARK, $matrix->get(6, $i));
				$this::assertSame(QRMatrix::M_TIMING | QRMatrix::IS_DARK, $matrix->get($i, 6));
			}
		}
	}

	/**
	 * Tests the version patterns and verifies their positions - version < 7 skipped
	 *
	 * @dataProvider versionProvider
	 */
	public function testSetVersionNumber(int $version):void{

		if($version < 7){
			$this::markTestSkipped('N/A (Version < 7)');
		}

		$matrix = $this->getMatrix($version)->setVersionNumber();

		$this::assertTrue($matrix->checkType($matrix->size() - 9, 0, QRMatrix::M_VERSION));
		$this::assertTrue($matrix->checkType($matrix->size() - 11, 5, QRMatrix::M_VERSION));
		$this::assertTrue($matrix->checkType(0, $matrix->size() - 9, QRMatrix::M_VERSION));
		$this::assertTrue($matrix->checkType(5, $matrix->size() - 11, QRMatrix::M_VERSION));
	}

	/**
	 * Tests the format patterns and verifies their positions
	 *
	 * @dataProvider versionProvider
	 */
	public function testSetFormatInfo(int $version):void{
		$matrix = $this->getMatrix($version)->setFormatInfo(new MaskPattern(MaskPattern::PATTERN_000));

		$this::assertTrue($matrix->checkType(8, 0, QRMatrix::M_FORMAT));
		$this::assertTrue($matrix->checkType(0, 8, QRMatrix::M_FORMAT));
		$this::assertTrue($matrix->checkType($matrix->size() - 1, 8, QRMatrix::M_FORMAT));
		$this::assertTrue($matrix->checkType($matrix->size() - 8, 8, QRMatrix::M_FORMAT));
	}

	/**
	 * Tests the quiet zone pattern and verifies its position
	 *
	 * @dataProvider versionProvider
	 */
	public function testSetQuietZone(int $version):void{
		$matrix = $this->getMatrix($version);

		$size = $matrix->size();
		$q    = 5;

		$matrix->set(0, 0, true, QRMatrix::M_TEST);
		$matrix->set($size - 1, $size - 1, true, QRMatrix::M_TEST);

		$matrix->setQuietZone($q);

		$this::assertCount($size + 2 * $q, $matrix->matrix());
		$this::assertCount($size + 2 * $q, $matrix->matrix()[$size - 1]);

		$size = $matrix->size();
		$this::assertTrue($matrix->checkType(0, 0, QRMatrix::M_QUIETZONE));
		$this::assertTrue($matrix->checkType($size - 1, $size - 1, QRMatrix::M_QUIETZONE));

		$this::assertSame(QRMatrix::M_TEST | QRMatrix::IS_DARK, $matrix->get($q, $q));
		$this::assertSame(QRMatrix::M_TEST | QRMatrix::IS_DARK, $matrix->get($size - 1 - $q, $size - 1 - $q));
	}

	/**
	 * Tests if an exception is thrown in an attempt to create the quiet zone before data was written
	 */
	public function testSetQuietZoneException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('use only after writing data');

		$this->matrix->setQuietZone();
	}

	/**
	 * Tests the auto orientation of the logo space
	 */
	public function testSetLogoSpaceOrientation():void{
		$o = new QROptions;
		$o->version      = 10;
		$o->eccLevel     = EccLevel::H;
		$o->addQuietzone = false;

		$matrix = (new QRCode($o))->addByteSegment('testdata')->getMatrix();
		// also testing size adjustment to uneven numbers
		$matrix->setLogoSpace(20, 14);

		// NW corner
		$this::assertFalse($matrix->checkType(17, 20, QRMatrix::M_LOGO));
		$this::assertTrue($matrix->checkType(18, 21, QRMatrix::M_LOGO));

		// SE corner
		$this::assertTrue($matrix->checkType(38, 35, QRMatrix::M_LOGO));
		$this::assertFalse($matrix->checkType(39, 36, QRMatrix::M_LOGO));
	}

	/**
	 * Tests the manual positioning of the logo space
	 */
	public function testSetLogoSpacePosition():void{
		$o = new QROptions;
		$o->version       = 10;
		$o->eccLevel      = EccLevel::H;
		$o->addQuietzone  = true;
		$o->quietzoneSize = 10;

		$m = (new QRCode($o))->addByteSegment('testdata')->getMatrix();

		// logo space should not overwrite quiet zone & function patterns
		$m->setLogoSpace(21, 21, -10, -10);
		$this::assertSame(QRMatrix::M_QUIETZONE, $m->get(9, 9));
		$this::assertSame(QRMatrix::M_FINDER | QRMatrix::IS_DARK, $m->get(10, 10));
		$this::assertSame(QRMatrix::M_FINDER | QRMatrix::IS_DARK, $m->get(16, 16));
		$this::assertSame(QRMatrix::M_SEPARATOR, $m->get(17, 17));
		$this::assertSame(QRMatrix::M_FORMAT | QRMatrix::IS_DARK, $m->get(18, 18));
		$this::assertSame(QRMatrix::M_LOGO, $m->get(19, 19));
		$this::assertSame(QRMatrix::M_LOGO, $m->get(20, 20));
		$this::assertNotSame(QRMatrix::M_LOGO, $m->get(21, 21));

		// i just realized that setLogoSpace() could be called multiple times
		// on the same instance and i'm not going to do anything about it :P
		$m->setLogoSpace(21, 21, 45, 45);
		$this::assertNotSame(QRMatrix::M_LOGO, $m->get(54, 54));
		$this::assertSame(QRMatrix::M_LOGO, $m->get(55, 55));
		$this::assertSame(QRMatrix::M_QUIETZONE, $m->get(67, 67));
	}

	/**
	 * Tests whether an exception is thrown when an ECC level other than "H" is set when attempting to add logo space
	 */
	public function testSetLogoSpaceInvalidEccException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('ECC level "H" required to add logo space');

		(new QRCode)->addByteSegment('testdata')->getMatrix()->setLogoSpace(50, 50);
	}

	/**
	 * Tests whether an exception is thrown when the logo space size exceeds the maximum ECC capacity
	 */
	public function testSetLogoSpaceMaxSizeException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('logo space exceeds the maximum error correction capacity');

		$o = new QROptions;
		$o->version  = 5;
		$o->eccLevel = EccLevel::H;

		(new QRCode($o))->addByteSegment('testdata')->getMatrix()->setLogoSpace(37, 37);
	}

	/**
	 * Tests flipping the value of a module
	 */
	public function testFlip():void{
		// using the dark module here because i'm lazy
		$matrix = $this->getMatrix(10)->setDarkModule();
		$x = 8;
		$y = $matrix->size() - 8;

		// cover checkType()
		$this::assertTrue($matrix->checkType($x, $y, QRMatrix::M_DARKMODULE));
		// verify the current state (dark)
		$this::assertSame(QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK, $matrix->get($x, $y));
		// flip
		$matrix->flip($x, $y);
		// verify flip
		$this::assertSame(QRMatrix::M_DARKMODULE, $matrix->get($x, $y));
		// flip again
		$matrix->flip($x, $y);
		// verify flip
		$this::assertSame(QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK, $matrix->get($x, $y));
	}

}
