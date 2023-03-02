<?php
/**
 * Class QRCodeTest
 *
 * @created      17.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\{QROptions, QRCode};
use chillerlan\QRCode\Output\{QRCodeOutputException, QROutputInterface};
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests basic functions of the QRCode class
 */
final class QRCodeTest extends TestCase{

	private QRCode    $qrcode;
	private QROptions $options;

	/**
	 * invoke test instances
	 */
	protected function setUp():void{
		$this->qrcode  = new QRCode;
		$this->options = new QROptions;
	}

	/**
	 * tests if an exception is thrown when an invalid (built-in) output type is specified
	 */
	public function testInitOutputInterfaceException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('invalid output module');

		$this->options->outputType = 'foo';

		$this->qrcode->setOptions($this->options)->render('test');
	}

	/**
	 * tests if an exception is thrown if the given output class does not exist
	 */
	public function testInitCustomOutputInterfaceNotExistsException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('invalid output module');

		$this->options->outputType = QROutputInterface::CUSTOM;

		$this->qrcode->setOptions($this->options)->render('test');
	}

	/**
	 * tests if an exception is thrown if the given output class does not implement QROutputInterface
	 */
	public function testInitCustomOutputInterfaceNotImplementsException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('output module does not implement QROutputInterface');

		$this->options->outputType      = QROutputInterface::CUSTOM;
		$this->options->outputInterface = stdClass::class;

		$this->qrcode->setOptions($this->options)->render('test');
	}

}
