<?php

/**
 *
 * @filesource   QRCodeTest.php
 * @created      08.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\GoogleAuth\Authenticator;
use chillerlan\QRCode\Output\{QRImage, QRImageOptions, QRString};
use chillerlan\QRCode\{QRCode, QROptions};
use ReflectionClass;
use PHPUnit\Framework\TestCase;

class QRCodeTest extends TestCase{

	/**
	 * @var \chillerlan\QRCode\QROptions
	 */
	protected $options;

	/**
	 * @var \chillerlan\QRCode\Output\QROutputInterface
	 */
	protected $output;

	/**
	 * @var \ReflectionClass
	 */
	protected $reflectionClass;

	protected function setUp(){
		$this->options = new QROptions;
		$this->output = new QRString;
		$this->reflectionClass = new ReflectionClass(QRCode::class);
	}

	public function testInstance(){
		$this->assertInstanceOf(QRString::class, $this->output);
		$this->assertInstanceOf(QROptions::class, $this->options);
		$this->assertInstanceOf(QRCode::class, $this->reflectionClass->newInstanceArgs(['foobar', $this->output, $this->options]));
	}

	public function stringDataProvider(){
		return [
			['1234567890', QRCode::TYPE_01],
			['ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', QRCode::TYPE_03],
			['#\\', QRCode::TYPE_01],
			['茗荷', QRCode::TYPE_01],
		];
	}

	/**
	 * @dataProvider stringDataProvider
	 */
	public function testDataCoverage($data){
		(new QRCode($data, $this->output))->getRawData();
		$this->markTestSkipped('code coverage');
	}

	/**
	 * @dataProvider stringDataProvider
	 */
	public function testTypeAndErrorcorrectlevelCoverage($data){

		foreach(range(1, 10) as $type){
			foreach(range(0, 3) as $eclevel){
				$this->options->typeNumber = $type;
				$this->options->errorCorrectLevel = $eclevel;
				$this->assertInstanceOf(QRCode::class, new QRCode($data, $this->output, $this->options));
			}
		}
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage No data given.
	 */
	public function testNoDataException(){
		$this->reflectionClass->newInstanceArgs(['', $this->output]);
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage Invalid error correct level: 42
	 */
	public function testErrorCorrectLevelException(){
		$this->options->errorCorrectLevel = 42;
		$this->reflectionClass->newInstanceArgs(['foobar', $this->output, $this->options]);
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage code length overflow. (261 > 72bit)
	 */
	public function testCodeLengthOverflowException(){
		$this->options->typeNumber = QRCode::TYPE_01;
		$this->options->errorCorrectLevel = QRCode::ERROR_CORRECT_LEVEL_H;

		$method = $this->reflectionClass->getMethod('getRawData');
		$method->invoke($this->reflectionClass->newInstanceArgs(['ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', $this->output, $this->options]));
	}

	public function testAuthenticatorExample(){
		$authenticator = new Authenticator;

		$data   = $authenticator->getUri($authenticator->createSecret(), 'test', 'chillerlan.net');
		$qrcode = $this->reflectionClass->newInstanceArgs([$data, new QRImage(new QRImageOptions(['type' => QRCode::OUTPUT_IMAGE_GIF]))]);

		$this->markTestSkipped(print_r($qrcode->output(), true));
	}

}
