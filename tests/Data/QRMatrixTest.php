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
use chillerlan\QRCodeTest\QRTestAbstract;
use ReflectionClass;

class QRMatrixTest extends QRTestAbstract{

	protected string $FQCN = QRMatrix::class;

	protected int $version = 7;

	protected QRMatrix $matrix;

	protected function setUp():void{
		parent::setUp();

		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->matrix = $this->reflection->newInstanceArgs([$this->version, QRCode::ECC_L]);
	}

	public function testInvalidVersionException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('invalid QR Code version');

		$this->reflection->newInstanceArgs([42, 0]);
	}

	public function testInvalidEccException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('invalid ecc level');

		$this->reflection->newInstanceArgs([1, 42]);
	}

	public function testInstance():void{
		$this::assertInstanceOf($this->FQCN, $this->matrix);
	}

	public function testSize():void{
		$this::assertCount($this->matrix->size(), $this->matrix->matrix());
	}

	public function testVersion():void{
		$this::assertSame($this->version, $this->matrix->version());
	}

	public function testECC():void{
		$this::assertSame(QRCode::ECC_L, $this->matrix->eccLevel());
	}

	public function testMaskPattern():void{
		$this::assertSame(-1, $this->matrix->maskPattern());
	}

	public function testGetSetCheck():void{
		$this->matrix->set(10, 10, true, QRMatrix::M_TEST);
		$this::assertSame(65280, $this->matrix->get(10, 10));
		$this::assertTrue($this->matrix->check(10, 10));

		$this->matrix->set(20, 20, false, QRMatrix::M_TEST);
		$this::assertSame(255, $this->matrix->get(20, 20));
		$this::assertFalse($this->matrix->check(20, 20));
	}

	public function testSetDarkModule():void{
		$this->matrix->setDarkModule();

		$this::assertSame(QRMatrix::M_DARKMODULE << 8, $this->matrix->get(8, $this->matrix->size() - 8));
	}

	public function testSetFinderPattern():void{
		$this->matrix->setFinderPattern();

		$this::assertSame(QRMatrix::M_FINDER << 8, $this->matrix->get(0, 0));
		$this::assertSame(QRMatrix::M_FINDER << 8, $this->matrix->get(0, $this->matrix->size() - 1));
		$this::assertSame(QRMatrix::M_FINDER << 8, $this->matrix->get($this->matrix->size() - 1, 0));
	}

	public function testSetSeparators():void{
		$this->matrix->setSeparators();

		$this::assertSame(QRMatrix::M_SEPARATOR, $this->matrix->get(7, 0));
		$this::assertSame(QRMatrix::M_SEPARATOR, $this->matrix->get(0, 7));
		$this::assertSame(QRMatrix::M_SEPARATOR, $this->matrix->get(0, $this->matrix->size() - 8));
		$this::assertSame(QRMatrix::M_SEPARATOR, $this->matrix->get($this->matrix->size() - 8, 0));
	}

	public function testSetAlignmentPattern():void{
		$this->matrix
			->setFinderPattern()
			->setAlignmentPattern()
		;

		$alignmentPattern = (new ReflectionClass(QRMatrix::class))->getConstant('alignmentPattern')[$this->version];

		foreach($alignmentPattern as $py){
			foreach($alignmentPattern as $px){

				if($this->matrix->get($px, $py) === QRMatrix::M_FINDER << 8){
					$this::assertSame(QRMatrix::M_FINDER << 8, $this->matrix->get($px, $py), 'skipped finder pattern');
					continue;
				}

				$this::assertSame(QRMatrix::M_ALIGNMENT << 8, $this->matrix->get($px, $py));
			}
		}

	}

	public function testSetTimingPattern():void{
		$this->matrix
			->setAlignmentPattern()
			->setTimingPattern()
		;

		$size = $this->matrix->size();

		for($i = 7; $i < $size - 7; $i++){
			if($i % 2 === 0){
				$p1 = $this->matrix->get(6, $i);

				if($p1 === QRMatrix::M_ALIGNMENT << 8){
					$this::assertSame(QRMatrix::M_ALIGNMENT << 8, $p1, 'skipped alignment pattern');
					continue;
				}

				$this::assertSame(QRMatrix::M_TIMING << 8, $p1);
				$this::assertSame(QRMatrix::M_TIMING << 8, $this->matrix->get($i, 6));
			}
		}
	}

	public function testSetVersionNumber():void{
		$this->matrix->setVersionNumber(true);

		$this::assertSame(QRMatrix::M_VERSION, $this->matrix->get($this->matrix->size() - 9, 0));
		$this::assertSame(QRMatrix::M_VERSION, $this->matrix->get($this->matrix->size() - 11, 5));
		$this::assertSame(QRMatrix::M_VERSION, $this->matrix->get(0, $this->matrix->size() - 9));
		$this::assertSame(QRMatrix::M_VERSION, $this->matrix->get(5, $this->matrix->size() - 11));
	}

	public function testSetFormatInfo():void{
		$this->matrix->setFormatInfo(0, true);

		$this::assertSame(QRMatrix::M_FORMAT, $this->matrix->get(8, 0));
		$this::assertSame(QRMatrix::M_FORMAT, $this->matrix->get(0, 8));
		$this::assertSame(QRMatrix::M_FORMAT, $this->matrix->get($this->matrix->size() - 1, 8));
		$this::assertSame(QRMatrix::M_FORMAT, $this->matrix->get($this->matrix->size() - 8, 8));
	}

	public function testSetQuietZone():void{
		$size = $this->matrix->size();
		$q    = 5;

		$this->matrix->set(0, 0, true, QRMatrix::M_TEST);
		$this->matrix->set($size - 1, $size - 1, true, QRMatrix::M_TEST);

		$this->matrix->setQuietZone($q);

		$this::assertCount($size + 2 * $q, $this->matrix->matrix());
		$this::assertCount($size + 2 * $q, $this->matrix->matrix()[$size - 1]);

		$size = $this->matrix->size();
		$this::assertSame(QRMatrix::M_QUIETZONE, $this->matrix->get(0, 0));
		$this::assertSame(QRMatrix::M_QUIETZONE, $this->matrix->get($size - 1, $size - 1));

		$this::assertSame(QRMatrix::M_TEST << 8, $this->matrix->get($q, $q));
		$this::assertSame(QRMatrix::M_TEST << 8, $this->matrix->get($size - 1 - $q, $size - 1 - $q));
	}

	public function testSetQuietZoneException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('use only after writing data');

		$this->matrix->setQuietZone();
	}

}
