<?php
/**
 *
 * @filesource   OutputTestAbstract.php
 * @created      17.12.2016
 * @package      chillerlan\QRCodeTest\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

/**
 * Class OutputTestAbstract
 */
abstract class OutputTestAbstract extends \PHPUnit_Framework_TestCase{

	protected $outputInterfaceClass;
	protected $outputOptionsClass;

	protected $options;
	protected $outputInterface;

	protected function setUp(){
		$this->options         = new $this->outputOptionsClass;
		$this->outputInterface = new $this->outputInterfaceClass($this->options);
	}

	public function testInstance(){
		$this->assertInstanceOf($this->outputInterfaceClass, $this->outputInterface);
		$this->assertInstanceOf($this->outputOptionsClass, $this->options);
	}

	/**
	 * @expectedException \chillerlan\QRCode\Output\QRCodeOutputException
	 * @expectedExceptionMessage Invalid output type!
	 */
	public function testOutputTypeException(){
		$this->options->type = 'foo';
		new $this->outputInterfaceClass($this->options);
	}

	/**
	 * @expectedException \chillerlan\QRCode\Output\QRCodeOutputException
	 * @expectedExceptionMessage Invalid matrix!
	 */
	public function testSetMatrixException(){
		/** @var \chillerlan\QRCode\Output\QROutputInterface  $outputInterface */
		$outputInterface = new $this->outputInterfaceClass;
		$outputInterface->setMatrix([]);
	}

}
