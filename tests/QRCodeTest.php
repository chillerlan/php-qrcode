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
use chillerlan\QRCode\Data\QRCodeDataException;
use chillerlan\QRCode\Output\QRCodeOutputException;
use PHPUnit\Framework\TestCase;

/**
 * Tests basic functions of the QRCode class
 */
final class QRCodeTest extends TestCase{

	private QRCode $qrcode;
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
		$this->expectExceptionMessage('invalid output type');

		$this->options->outputType = 'foo';

		(new QRCode($this->options))->render('test');
	}

	/**
	 * tests if an exception is thrown when trying to call getMatrix() without data (empty string, no data set)
	 */
	public function testGetMatrixException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('QRCode::getMatrix() No data given.');

		$this->qrcode->getMatrix();
	}

}
