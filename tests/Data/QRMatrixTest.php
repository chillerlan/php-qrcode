<?php
/**
 * Class QRMatrixTest
 *
 * @filesource   QRMatrixTest.php
 * @created      17.11.2017
 * @package      chillerlan\QRCodeTest\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Data\{QRCodeDataException, QRMatrix};
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests the QRMatix class
 */
final class QRMatrixTest extends TestCase{

	/** @internal */
	protected const version = 40;
	/** @internal */
	protected QRMatrix $matrix;

	/**
	 * invokes a QRMatrix object
	 *
	 * @internal
	 */
	protected function setUp():void{
		$this->matrix = $this->getMatrix($this::version);
	}

	/**
	 * shortcut
	 *
	 * @internal
	 */
	protected function getMatrix(int $version):QRMatrix{
		return  new QRMatrix($version, QRCode::ECC_L);
	}

	/**
	 * Validates the QRMatrix instance
	 */
	public function testInstance():void{
		$this::assertInstanceOf(QRMatrix::class, $this->matrix);
	}

	/**
	 * Tests if an exception is thrown when an invalid QR version was given
	 */
	public function testInvalidVersionException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('invalid QR Code version');

		$this->matrix = new QRMatrix(42, 0);
	}

	/**
	 * Tests if an exception is thrown when an invalid ECC level was given
	 */
	public function testInvalidEccException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('invalid ecc level');

		$this->matrix = new QRMatrix(1, 42);
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
		$this::assertSame($this::version, $this->matrix->version());
	}

	/**
	 * Tests if eccLevel() returns the current (given) ECC level
	 */
	public function testECC():void{
		$this::assertSame(QRCode::ECC_L, $this->matrix->eccLevel());
	}

	/**
	 * Tests if maskPattern() returns the current (or default) mask pattern
	 */
	public function testMaskPattern():void{
		$this::assertSame(-1, $this->matrix->maskPattern()); // default

		// @todo: actual mask pattern after mapData()
	}

	/**
	 * Tests the set(), get() and check() methods
	 */
	public function testGetSetCheck():void{
		$this->matrix->set(10, 10, true, QRMatrix::M_TEST);
		$this::assertSame(65280, $this->matrix->get(10, 10));
		$this::assertTrue($this->matrix->check(10, 10));

		$this->matrix->set(20, 20, false, QRMatrix::M_TEST);
		$this::assertSame(255, $this->matrix->get(20, 20));
		$this::assertFalse($this->matrix->check(20, 20));
	}

	/**
	 * Version data provider for several pattern tests
	 *
	 * @return int[][]
	 * @internal
	 */
	public function versionProvider():array{
		$versions = [];

		for($i = 1; $i <= 40; $i++){
			$versions[] = [$i];
		}

		return $versions;
	}

	/**
	 * Tests setting the dark module and verifies its position
	 *
	 * @dataProvider versionProvider
	 */
	public function testSetDarkModule(int $version):void{
		$matrix = $this->getMatrix($version)->setDarkModule();

		$this::assertSame(QRMatrix::M_DARKMODULE << 8, $matrix->get(8, $matrix->size() - 8));
	}

	/**
	 * Tests setting the finder patterns and verifies their positions
	 *
	 * @dataProvider versionProvider
	 */
	public function testSetFinderPattern(int $version):void{
		$matrix = $this->getMatrix($version)->setFinderPattern();

		$this::assertSame(QRMatrix::M_FINDER << 8, $matrix->get(0, 0));
		$this::assertSame(QRMatrix::M_FINDER << 8, $matrix->get(0, $matrix->size() - 1));
		$this::assertSame(QRMatrix::M_FINDER << 8, $matrix->get($matrix->size() - 1, 0));
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
			$this->markTestSkipped('N/A');
			return;
		}

		$matrix = $this
			->getMatrix($version)
			->setFinderPattern()
			->setAlignmentPattern()
		;

		$alignmentPattern = (new ReflectionClass(QRMatrix::class))->getConstant('alignmentPattern')[$version];

		foreach($alignmentPattern as $py){
			foreach($alignmentPattern as $px){

				if($matrix->get($px, $py) === QRMatrix::M_FINDER << 8){
					$this::assertSame(QRMatrix::M_FINDER << 8, $matrix->get($px, $py), 'skipped finder pattern');
					continue;
				}

				$this::assertSame(QRMatrix::M_ALIGNMENT << 8, $matrix->get($px, $py));
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
				$p1 = $matrix->get(6, $i);

				if($p1 === QRMatrix::M_ALIGNMENT << 8){
					$this::assertSame(QRMatrix::M_ALIGNMENT << 8, $p1, 'skipped alignment pattern');
					continue;
				}

				$this::assertSame(QRMatrix::M_TIMING << 8, $p1);
				$this::assertSame(QRMatrix::M_TIMING << 8, $matrix->get($i, 6));
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
			$this->markTestSkipped('N/A');
			return;
		}

		$matrix = $this->getMatrix($version)->setVersionNumber(true);

		$this::assertSame(QRMatrix::M_VERSION, $matrix->get($matrix->size() - 9, 0));
		$this::assertSame(QRMatrix::M_VERSION, $matrix->get($matrix->size() - 11, 5));
		$this::assertSame(QRMatrix::M_VERSION, $matrix->get(0, $matrix->size() - 9));
		$this::assertSame(QRMatrix::M_VERSION, $matrix->get(5, $matrix->size() - 11));
	}

	/**
	 * Tests the format patterns and verifies their positions
	 *
	 * @dataProvider versionProvider
	 */
	public function testSetFormatInfo(int $version):void{
		$matrix = $this->getMatrix($version)->setFormatInfo(0, true);

		$this::assertSame(QRMatrix::M_FORMAT, $matrix->get(8, 0));
		$this::assertSame(QRMatrix::M_FORMAT, $matrix->get(0, 8));
		$this::assertSame(QRMatrix::M_FORMAT, $matrix->get($matrix->size() - 1, 8));
		$this::assertSame(QRMatrix::M_FORMAT, $matrix->get($matrix->size() - 8, 8));
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
		$this::assertSame(QRMatrix::M_QUIETZONE, $matrix->get(0, 0));
		$this::assertSame(QRMatrix::M_QUIETZONE, $matrix->get($size - 1, $size - 1));

		$this::assertSame(QRMatrix::M_TEST << 8, $matrix->get($q, $q));
		$this::assertSame(QRMatrix::M_TEST << 8, $matrix->get($size - 1 - $q, $size - 1 - $q));
	}

	/**
	 * Tests if an exception is thrown in an attempt to create it before data was written
	 */
	public function testSetQuietZoneException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('use only after writing data');

		$this->matrix->setQuietZone();
	}

}
