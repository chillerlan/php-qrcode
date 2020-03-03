<?php
/**
 * Class DatainterfaceTestAbstract
 *
 * @filesource   DatainterfaceTestAbstract.php
 * @created      24.11.2017
 * @package      chillerlan\QRCodeTest\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\{QRCodeDataException, QRDataInterface, QRMatrix};
use chillerlan\QRCodeTest\QRTestAbstract;

use function str_repeat;

abstract class DatainterfaceTestAbstract extends QRTestAbstract{

	protected QRDataInterface $dataInterface;

	protected string $testdata;

	protected array  $expected;

	protected function setUp():void{
		parent::setUp();

		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->dataInterface = $this->reflection->newInstanceArgs([new QROptions(['version' => 4])]);
	}

	public function testInstance():void{
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->dataInterface = $this->reflection->newInstanceArgs([new QROptions, $this->testdata]);

		$this::assertInstanceOf(QRDataInterface::class, $this->dataInterface);
	}

	public function testSetData():void{
		$this->dataInterface->setData($this->testdata);

		$this::assertSame($this->expected, $this->getProperty('matrixdata')->getValue($this->dataInterface));
	}

	public function testInitMatrix():void{
		$m = $this->dataInterface->setData($this->testdata)->initMatrix(0);

		$this::assertInstanceOf(QRMatrix::class, $m);
	}

	public function testGetMinimumVersion():void{
		$this::assertSame(1, $this->getMethod('getMinimumVersion')->invoke($this->dataInterface));
	}

	public function testGetMinimumVersionException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('data exceeds');

		$this->getProperty('strlen')->setValue($this->dataInterface, 13370);
		$this->getMethod('getMinimumVersion')->invoke($this->dataInterface);
	}

	public function testCodeLengthOverflowException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('code length overflow');

		$this->dataInterface->setData(str_repeat('0', 1337));
	}

}
