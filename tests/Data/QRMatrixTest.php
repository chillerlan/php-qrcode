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

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\{EccLevel, MaskPattern, Version};
use chillerlan\QRCode\Data\{QRCodeDataException, QRMatrix};
use chillerlan\QRCode\Output\{QROutputInterface, QRString};
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Color;
use Generator;
use function defined;

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
		$this->matrix = new QRMatrix(
			new Version($this::version),
			new EccLevel(EccLevel::L),
			new MaskPattern(MaskPattern::PATTERN_000)
		);
	}

	/**
	 * Matrix debugging console output
	 */
	public static function debugMatrix(QRMatrix $matrix):void{

		if(defined('TEST_IS_CI') && TEST_IS_CI === true){
			return;
		}

		$opt = new QROptions;
		$opt->outputType  = QROutputInterface::STRING_TEXT;
		$opt->eol         = Color::colorize('reset', "\x00\n");
		$opt->moduleValues = [
			// finder
			QRMatrix::M_FINDER | QRMatrix::IS_DARK     => Color::colorize('fg-black', '🔴'), // dark (true)
			QRMatrix::M_FINDER                         => Color::colorize('fg-black', '⭕'), // light (false)
			QRMatrix::M_FINDER_DOT | QRMatrix::IS_DARK => Color::colorize('fg-black', '🔴'), // finder dot, dark (true)
			// alignment
			QRMatrix::M_ALIGNMENT | QRMatrix::IS_DARK  => Color::colorize('fg-blue', '🔴'),
			QRMatrix::M_ALIGNMENT                      => Color::colorize('fg-blue', '⭕'),
			// timing
			QRMatrix::M_TIMING | QRMatrix::IS_DARK     => Color::colorize('fg-red', '🔴'),
			QRMatrix::M_TIMING                         => Color::colorize('fg-red', '⭕'),
			// format
			QRMatrix::M_FORMAT | QRMatrix::IS_DARK     => Color::colorize('fg-magenta', '🔴'),
			QRMatrix::M_FORMAT                         => Color::colorize('fg-magenta', '⭕'),
			// version
			QRMatrix::M_VERSION | QRMatrix::IS_DARK    => Color::colorize('fg-green', '🔴'),
			QRMatrix::M_VERSION                        => Color::colorize('fg-green', '⭕'),
			// data
			QRMatrix::M_DATA | QRMatrix::IS_DARK       => Color::colorize('fg-white', '🔴'),
			QRMatrix::M_DATA                           => Color::colorize('fg-white', '⭕'),
			// darkmodule
			QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK => Color::colorize('fg-black', '🔴'),
			// separator
			QRMatrix::M_SEPARATOR                      => Color::colorize('fg-cyan', '⭕'),
			// quietzone
			QRMatrix::M_QUIETZONE                      => Color::colorize('fg-cyan', '⭕'),
			// logo space
			QRMatrix::M_LOGO                           => Color::colorize('fg-yellow', '⭕'),
			// empty
			QRMatrix::M_NULL                           => Color::colorize('fg-black', '⭕'),
			// data
			QRMatrix::M_TEST | QRMatrix::IS_DARK       => Color::colorize('fg-white', '🔴'),
			QRMatrix::M_TEST                           => Color::colorize('fg-black', '⭕'),
		];

		$out = (new QRString($opt, $matrix))->dump();

		echo "\n\n".$out."\n\n";
	}

	/**
	 * debugging shortcut qirth limit to a single version when using with matrixProvider
	 *
	 * @see QRMatrixTest::matrixProvider()
	 */
	protected function dm(QRMatrix $matrix):void{

		// limit
		if($matrix->version()->getVersionNumber() !== 7){
			return;
		}

		self::debugMatrix($matrix);
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
		// set via matrix evaluation
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

		// out of range
		$this::assertFalse($this->matrix->check(-1, -1));
		$this::assertSame(-1, $this->matrix->get(-1, -1));
	}

	/**
	 * Version data provider for several pattern tests
	 */
	public function matrixProvider():Generator{
		$ecc  = new EccLevel(EccLevel::L);
		$mask = new MaskPattern(MaskPattern::PATTERN_000);

		foreach(range(1, 40) as $i){
			yield 'version: '.$i => [new QRMatrix(new Version($i), $ecc, $mask)];
		}
	}

	/**
	 * Tests setting the dark module and verifies its position
	 *
	 * @dataProvider matrixProvider
	 */
	public function testSetDarkModule(QRMatrix $matrix):void{
		$matrix->setDarkModule();

		$this::assertSame(QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK, $matrix->get(8, $matrix->size() - 8));

		$this->dm($matrix);
	}

	/**
	 * Tests setting the finder patterns and verifies their positions
	 *
	 * @dataProvider matrixProvider
	 */
	public function testSetFinderPattern(QRMatrix $matrix):void{
		$matrix->setFinderPattern();

		$this::assertSame(QRMatrix::M_FINDER | QRMatrix::IS_DARK, $matrix->get(0, 0));
		$this::assertSame(QRMatrix::M_FINDER | QRMatrix::IS_DARK, $matrix->get(0, $matrix->size() - 1));
		$this::assertSame(QRMatrix::M_FINDER | QRMatrix::IS_DARK, $matrix->get($matrix->size() - 1, 0));

		$this->dm($matrix);
	}

	/**
	 * Tests the separator patterns and verifies their positions
	 *
	 * @dataProvider matrixProvider
	 */
	public function testSetSeparators(QRMatrix $matrix):void{
		$matrix->setSeparators();

		$this::assertSame(QRMatrix::M_SEPARATOR, $matrix->get(7, 0));
		$this::assertSame(QRMatrix::M_SEPARATOR, $matrix->get(0, 7));
		$this::assertSame(QRMatrix::M_SEPARATOR, $matrix->get(0, $matrix->size() - 8));
		$this::assertSame(QRMatrix::M_SEPARATOR, $matrix->get($matrix->size() - 8, 0));

		$this->dm($matrix);
	}

	/**
	 * Tests the alignment patterns and verifies their positions - version 1 (no pattern) skipped
	 *
	 * @dataProvider matrixProvider
	 */
	public function testSetAlignmentPattern(QRMatrix $matrix):void{
		$version = $matrix->version();

		if($version->getVersionNumber() === 1){
			$this::markTestSkipped('N/A (Version 1 has no alignment pattern)');
		}

		$matrix
			->setFinderPattern()
			->setAlignmentPattern()
		;

		$alignmentPattern = $version->getAlignmentPattern();

		foreach($alignmentPattern as $py){
			foreach($alignmentPattern as $px){
				// skip finder pattern
				if($matrix->checkTypeNotIn($px, $py, [QRMatrix::M_FINDER, QRMatrix::M_FINDER_DOT])){
					$this::assertSame(QRMatrix::M_ALIGNMENT | QRMatrix::IS_DARK, $matrix->get($px, $py));
				}
			}
		}

		$this->dm($matrix);
	}

	/**
	 * Tests the timing patterns and verifies their positions
	 *
	 * @dataProvider matrixProvider
	 */
	public function testSetTimingPattern(QRMatrix $matrix):void{

		$matrix
			->setFinderPattern()
			->setAlignmentPattern()
			->setTimingPattern()
		;

		$size = $matrix->size();

		for($i = 7; $i < $size - 7; $i++){
			if($i % 2 === 0){
				// skip alignment pattern
				if($matrix->checkTypeNotIn(6, $i, [QRMatrix::M_ALIGNMENT])){
					$this::assertSame(QRMatrix::M_TIMING | QRMatrix::IS_DARK, $matrix->get(6, $i));
					$this::assertSame(QRMatrix::M_TIMING | QRMatrix::IS_DARK, $matrix->get($i, 6));
				}
			}
		}

		$this->dm($matrix);
	}

	/**
	 * Tests the version patterns and verifies their positions - version < 7 skipped
	 *
	 * @dataProvider matrixProvider
	 */
	public function testSetVersionNumber(QRMatrix $matrix):void{

		if($matrix->version()->getVersionNumber() < 7){
			$this::markTestSkipped('N/A (Version < 7)');
		}

		$matrix->setVersionNumber();

		$this::assertTrue($matrix->checkType($matrix->size() - 9, 0, QRMatrix::M_VERSION));
		$this::assertTrue($matrix->checkType($matrix->size() - 11, 5, QRMatrix::M_VERSION));
		$this::assertTrue($matrix->checkType(0, $matrix->size() - 9, QRMatrix::M_VERSION));
		$this::assertTrue($matrix->checkType(5, $matrix->size() - 11, QRMatrix::M_VERSION));

		$this->dm($matrix);
	}

	/**
	 * Tests the format patterns and verifies their positions
	 *
	 * @dataProvider matrixProvider
	 */
	public function testSetFormatInfo(QRMatrix $matrix):void{
		$matrix->setFormatInfo();

		$this::assertTrue($matrix->checkType(8, 0, QRMatrix::M_FORMAT));
		$this::assertTrue($matrix->checkType(0, 8, QRMatrix::M_FORMAT));
		$this::assertTrue($matrix->checkType($matrix->size() - 1, 8, QRMatrix::M_FORMAT));
		$this::assertTrue($matrix->checkType($matrix->size() - 8, 8, QRMatrix::M_FORMAT));

		$this->dm($matrix);
	}

	/**
	 * Tests the quiet zone pattern and verifies its position
	 *
	 * @dataProvider matrixProvider
	 */
	public function testSetQuietZone(QRMatrix $matrix):void{
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

		$this->dm($matrix);
	}

	/**
	 * Tests if an exception is thrown in an attempt to create the quiet zone before data was written
	 */
	public function testSetQuietZoneException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('use only after writing data');

		$this->matrix->setQuietZone(42);
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

		self::debugMatrix($matrix);
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

		$matrix = (new QRCode($o))->addByteSegment('testdata')->getMatrix();

		// logo space should not overwrite quiet zone & function patterns
		$matrix->setLogoSpace(21, 21, -10, -10);
		$this::assertSame(QRMatrix::M_QUIETZONE, $matrix->get(9, 9));
		$this::assertSame(QRMatrix::M_FINDER | QRMatrix::IS_DARK, $matrix->get(10, 10));
		$this::assertSame(QRMatrix::M_FINDER | QRMatrix::IS_DARK, $matrix->get(16, 16));
		$this::assertSame(QRMatrix::M_SEPARATOR, $matrix->get(17, 17));
		$this::assertSame(QRMatrix::M_FORMAT | QRMatrix::IS_DARK, $matrix->get(18, 18));
		$this::assertSame(QRMatrix::M_LOGO, $matrix->get(19, 19));
		$this::assertSame(QRMatrix::M_LOGO, $matrix->get(20, 20));
		$this::assertNotSame(QRMatrix::M_LOGO, $matrix->get(21, 21));

		// i just realized that setLogoSpace() could be called multiple times
		// on the same instance and i'm not going to do anything about it :P
		$matrix->setLogoSpace(21, 21, 45, 45);
		$this::assertNotSame(QRMatrix::M_LOGO, $matrix->get(54, 54));
		$this::assertSame(QRMatrix::M_LOGO, $matrix->get(55, 55));
		$this::assertSame(QRMatrix::M_QUIETZONE, $matrix->get(67, 67));

		self::debugMatrix($matrix);
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
	 * Tests whether an exception is thrown when width or height exceed the matrix size
	 */
	public function testSetLogoSpaceExceedsException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('logo dimensions exceed matrix size');

		$o = new QROptions;
		$o->version  = 5;
		$o->eccLevel = EccLevel::H;

		(new QRCode($o))->addByteSegment('testdata')->getMatrix()->setLogoSpace(69, 1);
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
		$this->matrix->setDarkModule();
		$x = 8;
		$y = $this->matrix->size() - 8;

		// cover checkType()
		$this::assertTrue($this->matrix->checkType($x, $y, QRMatrix::M_DARKMODULE));
		// verify the current state (dark)
		$this::assertSame(QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK, $this->matrix->get($x, $y));
		// flip
		$this->matrix->flip($x, $y);
		// verify flip
		$this::assertSame(QRMatrix::M_DARKMODULE, $this->matrix->get($x, $y));
		// flip again
		$this->matrix->flip($x, $y);
		// verify flip
		$this::assertSame(QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK, $this->matrix->get($x, $y));
	}

	/**
	 * Tests checking whether the M_TYPE of a module is not one of an array of M_TYPES
	 */
	public function testCheckTypeNotIn():void{
		$this->matrix->set(10, 10, true, QRMatrix::M_QUIETZONE);

		$this::assertTrue($this->matrix->checkTypeNotIn(10, 10, [QRMatrix::M_DATA, QRMatrix::M_FINDER]));
		$this::assertFalse($this->matrix->checkTypeNotIn(10, 10, [QRMatrix::M_QUIETZONE, QRMatrix::M_FINDER]));
	}

	/**
	 * Tests checking the adjacent modules
	 */
	public function testCheckNeighbours():void{

		$this->matrix
			->setFinderPattern()
			->setAlignmentPattern()
		;

		/*
		 * center of finder pattern (surrounded by all dark)
		 *
		 *   # # # # # # #
		 *   #           #
		 *   #   # # #   #
		 *   #   # 0 #   #
		 *   #   # # #   #
		 *   #           #
		 *   # # # # # # #
		 */
		$this::assertSame(0b11111111, $this->matrix->checkNeighbours(3, 3));

		/*
		 * center of alignment pattern (surrounded by all light)
		 *
		 *   # # # # #
		 *   #       #
		 *   #   0   #
		 *   #       #
		 *   # # # # #
		 */
		$this::assertSame(0b00000000, $this->matrix->checkNeighbours(30, 30));

		/*
		 * top left light block of finder pattern
		 *
		 *   # # #
		 *   # 0
		 *   #   #
		 */
		$this::assertSame(0b11010111, $this->matrix->checkNeighbours(1, 1));

		/*
		 * bottom left light block of finder pattern
		 *
		 *   #   #
		 *   # 0
		 *   # # #
		 */
		$this::assertSame(0b11110101, $this->matrix->checkNeighbours(1, 5));

		/*
		 * top right light block of finder pattern
		 *
		 *   # # #
		 *     0 #
		 *   #   #
		 */
		$this::assertSame(0b01011111, $this->matrix->checkNeighbours(5, 1));

		/*
		 * bottom right light block of finder pattern
		 *
		 *   #   #
		 *     0 #
		 *   # # #
		 */
		$this::assertSame(0b01111101, $this->matrix->checkNeighbours(5, 5));


		/*
		 * M_TYPE check
		 *
		 *   # # #
		 *     0
		 *   X X X
		 */
		$this::assertSame(0b00000111, $this->matrix->checkNeighbours(3, 1, QRMatrix::M_FINDER));
		$this::assertSame(0b01110000, $this->matrix->checkNeighbours(3, 1, QRMatrix::M_FINDER_DOT));
	}
}
