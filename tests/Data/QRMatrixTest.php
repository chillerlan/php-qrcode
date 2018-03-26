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

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCodeTest\QRTestAbstract;

class QRMatrixTest extends QRTestAbstract{

	protected $FQCN = QRMatrix::class;

	protected $version = 7;

	/**
	 * @link http://www.thonky.com/qr-code-tutorial/format-version-tables
	 */
	const VERSION_REF = [
		7  => '000111110010010100',
		8  => '001000010110111100',
		9  => '001001101010011001',
		10 => '001010010011010011',
		11 => '001011101111110110',
		12 => '001100011101100010',
		13 => '001101100001000111',
		14 => '001110011000001101',
		15 => '001111100100101000',
		16 => '010000101101111000',
		17 => '010001010001011101',
		18 => '010010101000010111',
		19 => '010011010100110010',
		20 => '010100100110100110',
		21 => '010101011010000011',
		22 => '010110100011001001',
		23 => '010111011111101100',
		24 => '011000111011000100',
		25 => '011001000111100001',
		26 => '011010111110101011',
		27 => '011011000010001110',
		28 => '011100110000011010',
		29 => '011101001100111111',
		30 => '011110110101110101',
		31 => '011111001001010000',
		32 => '100000100111010101',
		33 => '100001011011110000',
		34 => '100010100010111010',
		35 => '100011011110011111',
		36 => '100100101100001011',
		37 => '100101010000101110',
		38 => '100110101001100100',
		39 => '100111010101000001',
		40 => '101000110001101001'
	];

	/**
	 * @var \chillerlan\QRCode\Data\QRMatrix
	 */
	protected $matrix;

	protected function setUp(){
		parent::setUp();

		$this->matrix = $this->reflection->newInstanceArgs([$this->version, QRCode::ECC_L]);
	}

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage invalid QR Code version
	 */
	public function testInvalidVersionException(){
		$this->reflection->newInstanceArgs([42, 0]);
	}

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage invalid ecc level
	 */
	public function testInvalidEccException(){
		$this->reflection->newInstanceArgs([1, 42]);
	}

	public function testInstance(){
		$this->assertInstanceOf($this->FQCN, $this->matrix);
	}

	public function testSize(){
		$this->assertCount($this->matrix->size(), $this->matrix->matrix());
	}

	public function testVersion(){
		$this->assertSame($this->version, $this->matrix->version());
	}

	public function testVersionPattern() {
		foreach (self::VERSION_REF as $version => $mask) {
			$hexRef = base_convert(self::VERSION_REF[$version],2 ,16);
			$hexImpl = dechex(QRMatrix::versionPattern[$version]);

			$this->assertEquals($hexRef, $hexImpl);
		}
	}

	public function testECC(){
		$this->assertSame(QRCode::ECC_L, $this->matrix->eccLevel());
	}

	public function testMaskPattern(){
		$this->assertSame(-1, $this->matrix->maskPattern());
	}

	public function testGetSetCheck(){
		$this->matrix->set(10, 10, true, QRMatrix::M_TEST);
		$this->assertSame(65280, $this->matrix->get(10, 10));
		$this->assertTrue($this->matrix->check(10, 10));

		$this->matrix->set(20, 20, false, QRMatrix::M_TEST);
		$this->assertSame(255, $this->matrix->get(20, 20));
		$this->assertFalse($this->matrix->check(20, 20));
	}

	public function testSetDarkModule(){
		$this->matrix->setDarkModule();

		$this->assertSame(QRMatrix::M_DARKMODULE << 8, $this->matrix->get(8, $this->matrix->size() - 8));
	}

	public function testSetFinderPattern(){
		$this->matrix->setFinderPattern();

		$this->assertSame(QRMatrix::M_FINDER << 8, $this->matrix->get(0, 0));
		$this->assertSame(QRMatrix::M_FINDER << 8, $this->matrix->get(0, $this->matrix->size() - 1));
		$this->assertSame(QRMatrix::M_FINDER << 8, $this->matrix->get($this->matrix->size() - 1, 0));
	}

	public function testSetSeparators(){
		$this->matrix->setSeparators();

		$this->assertSame(QRMatrix::M_SEPARATOR, $this->matrix->get(7, 0));
		$this->assertSame(QRMatrix::M_SEPARATOR, $this->matrix->get(0, 7));
		$this->assertSame(QRMatrix::M_SEPARATOR, $this->matrix->get(0, $this->matrix->size() - 8));
		$this->assertSame(QRMatrix::M_SEPARATOR, $this->matrix->get($this->matrix->size() - 8, 0));
	}

	public function testSetAlignmentPattern(){
		$this->matrix
			->setFinderPattern()
			->setAlignmentPattern()
		;

		$alignmentPattern = QRMatrix::alignmentPattern[$this->version];

		foreach($alignmentPattern as $py){
			foreach($alignmentPattern as $px){

				if($this->matrix->get($px, $py) === QRMatrix::M_FINDER << 8){
					$this->assertSame(QRMatrix::M_FINDER << 8, $this->matrix->get($px, $py), 'skipped finder pattern');
					continue;
				}

				$this->assertSame(QRMatrix::M_ALIGNMENT << 8, $this->matrix->get($px, $py));
			}
		}

	}

	public function testSetTimingPattern(){
		$this->matrix
			->setAlignmentPattern()
			->setTimingPattern()
		;

		$size = $this->matrix->size();

		for($i = 7; $i < $size - 7; $i++){
			if($i % 2 === 0){
				$p1 = $this->matrix->get(6, $i);

				if($p1 === QRMatrix::M_ALIGNMENT << 8){
					$this->assertSame(QRMatrix::M_ALIGNMENT << 8, $p1, 'skipped alignment pattern');
					continue;
				}

				$this->assertSame(QRMatrix::M_TIMING << 8, $p1);
				$this->assertSame(QRMatrix::M_TIMING << 8, $this->matrix->get($i, 6));
			}
		}
	}

	public function testSetVersionNumber(){
		$this->matrix->setVersionNumber(true);

		$this->assertSame(QRMatrix::M_VERSION, $this->matrix->get($this->matrix->size() - 9, 0));
		$this->assertSame(QRMatrix::M_VERSION, $this->matrix->get($this->matrix->size() - 11, 5));
		$this->assertSame(QRMatrix::M_VERSION, $this->matrix->get(0, $this->matrix->size() - 9));
		$this->assertSame(QRMatrix::M_VERSION, $this->matrix->get(5, $this->matrix->size() - 11));
	}

	public function testSetFormatInfo(){
		$this->matrix->setFormatInfo(0, true);

		$this->assertSame(QRMatrix::M_FORMAT, $this->matrix->get(8, 0));
		$this->assertSame(QRMatrix::M_FORMAT, $this->matrix->get(0, 8));
		$this->assertSame(QRMatrix::M_FORMAT, $this->matrix->get($this->matrix->size() - 1, 8));
		$this->assertSame(QRMatrix::M_FORMAT, $this->matrix->get($this->matrix->size() - 8, 8));
	}

	public function testSetQuietZone(){
		$size = $this->matrix->size();
		$q    = 5;

		$this->matrix->set(0, 0, true, QRMatrix::M_TEST);
		$this->matrix->set($size - 1, $size - 1, true, QRMatrix::M_TEST);

		$this->matrix->setQuietZone($q);

		$this->assertCount($size + 2 * $q, $this->matrix->matrix());
		$this->assertCount($size + 2 * $q, $this->matrix->matrix()[$size - 1]);

		$size = $this->matrix->size();
		$this->assertSame(QRMatrix::M_QUIETZONE, $this->matrix->get(0, 0));
		$this->assertSame(QRMatrix::M_QUIETZONE, $this->matrix->get($size - 1, $size - 1));

		$this->assertSame(QRMatrix::M_TEST << 8, $this->matrix->get($q, $q));
		$this->assertSame(QRMatrix::M_TEST << 8, $this->matrix->get($size - 1 - $q, $size - 1 - $q));
	}

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage use only after writing data
	 */
	public function testSetQuietZoneException(){
		$this->matrix->setQuietZone();
	}

}
