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
use chillerlan\QRCodeTest\QRTestAbstract;

class QRMatrixTest extends QRTestAbstract{

	protected $FQCN = QRMatrix::class;

	public function testInstance(){
		$q = $this->reflection->newInstanceArgs([1, 0]);
		$this->assertCount($q->size(), $q->matrix());
		$this->assertInstanceOf($this->FQCN, $q);
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

/*	public function testSetFinderPattern(){
		$q = $this->reflection->newInstanceArgs([1, 0]);
		$q->setFinderPattern();
		var_dump($q->matrix());
	}*/
}
