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

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use PHPUnit\Framework\TestCase;
use chillerlan\QRCode\Data\{QRCodeDataException, QRData, QRMatrix};
use ReflectionClass;

use function str_repeat;

/**
 * The data interface test abstract
 */
abstract class DatainterfaceTestAbstract extends TestCase{

	/** @internal */
	protected ReflectionClass $reflection;
	/** @internal */
	protected QRData $dataInterface;
	/** @internal */
	protected array $testdata;
	/** @internal */
	protected array $expected;

	/**
	 * @internal
	 */
	protected function setUp():void{
		$this->dataInterface = new QRData(new QROptions(['version' => 4]), []);
		$this->reflection    = new ReflectionClass($this->dataInterface);
	}

	/**
	 * Verifies the data interface instance
	 */
	public function testInstance():void{
		$this::assertInstanceOf(QRData::class, $this->dataInterface);
	}

	/**
	 * Tests ecc masking and verifies against a sample
	 */
/*	public function testMaskEcc():void{
		$this->dataInterface->setData([$this->testdata]);

		$maskECC = $this->reflection->getMethod('maskECC');
		$maskECC->setAccessible(true);

		$bitBuffer = $this->reflection->getProperty('bitBuffer');
		$bitBuffer->setAccessible(true);
		$bb = $bitBuffer->getValue($this->dataInterface);

		$this::assertSame($this->expected, $maskECC->invokeArgs($this->dataInterface, [$bb->getBuffer()]));
	}*/

	/**
	 * @see testInitMatrix()
	 * @internal
	 * @return int[][]
	 */
	public function MaskPatternProvider():array{
		return [[0], [1], [2], [3], [4], [5], [6], [7]];
	}

	/**
	 * Tests initializing the data matrix
	 *
	 * @dataProvider MaskPatternProvider
	 */
	public function testInitMatrix(int $maskPattern):void{
		$this->dataInterface->setData([$this->testdata]);

		$matrix = $this->dataInterface->writeMatrix($maskPattern);

		$this::assertInstanceOf(QRMatrix::class, $matrix);
		$this::assertSame($maskPattern, $matrix->maskPattern());
	}

	/**
	 * Tests getting the minimum QR version for the given data
	 */
	public function testGetMinimumVersion():void{
		$this->dataInterface->setData([$this->testdata]);

		$getMinimumVersion = $this->reflection->getMethod('getMinimumVersion');
		$getMinimumVersion->setAccessible(true);

		$this::assertSame(1, $getMinimumVersion->invoke($this->dataInterface));
	}

	/**
	 * Tests if an exception is thrown when the data exceeds the maximum version while auto detecting
	 */
	public function testGetMinimumVersionException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('data exceeds');
		[$class, $data] = $this->testdata;

		$this->dataInterface = new QRData(
			new QROptions(['version' => QRCode::VERSION_AUTO]),
			[[$class, str_repeat($data, 1337)]]
		);
	}

	/**
	 * Tests if an exception is thrown on data overflow
	 */
	public function testCodeLengthOverflowException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('code length overflow');
		[$class, $data] = $this->testdata;

		$this->dataInterface->setData([[$class, str_repeat($data, 1337)]]);
	}

}
