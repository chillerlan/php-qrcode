<?php
/**
 * Class QROutputTestAbstract
 *
 * @filesource   QROutputTestAbstract.php
 * @created      24.12.2017
 * @package      chillerlan\QRCodeTest\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\Byte;
use chillerlan\QRCode\Output\{QRCodeOutputException, QROutputInterface};
use chillerlan\QRCodeTest\QRTestAbstract;

use function dirname, file_exists, mkdir;

abstract class QROutputTestAbstract extends QRTestAbstract{

	const cachefile = __DIR__.'/../../.build/output_test/test.';

	/**
	 * @var \chillerlan\QRCode\Output\QROutputInterface
	 */
	protected $outputInterface;

	/**
	 * @var \chillerlan\QRCode\QROptions
	 */
	protected $options;

	/**
	 * @var \chillerlan\QRCode\Data\QRMatrix
	 */
	protected $matrix;

	protected function setUp():void{
		parent::setUp();

		$buildDir = dirname($this::cachefile);
		if(!file_exists($buildDir)){
			mkdir($buildDir, 0777, true);
		}

		$this->options = new QROptions;
		$this->setOutputInterface();
	}

	protected function setOutputInterface(){
		$this->outputInterface = $this->reflection->newInstanceArgs([$this->options, (new Byte($this->options, 'testdata'))->initMatrix(0)]);
		return $this->outputInterface;
	}

	public function testInstance(){
		$this->assertInstanceOf(QROutputInterface::class, $this->outputInterface);
	}

	public function testSaveException(){
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('Could not write data to cache file: /foo');

		$this->options->cachefile = '/foo';
		$this->setOutputInterface();
		$this->outputInterface->dump();
	}

}
