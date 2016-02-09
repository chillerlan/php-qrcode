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

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QRConst;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRImage;
use chillerlan\QRCode\Output\QRImageOptions;
use chillerlan\QRCode\Output\QRString;
use chillerlan\QRCode\Output\QRStringOptions;

class QRCodeTest extends \PHPUnit_Framework_TestCase{

	/**
	 * @var \chillerlan\QRCode\QROptions
	 */
	protected $options;

	/**
	 * @var \chillerlan\QRCode\Output\QROutputInterface
	 */
	protected $output;

	protected function setUp(){
		$this->options = new QROptions;
		$this->output = new QRString;
	}

	public function testInstance(){
		$this->assertInstanceOf(QRString::class, $this->output);
		$this->assertInstanceOf(QROptions::class, $this->options);
		$this->assertInstanceOf(QRCode::class, new QRCode('foobar', $this->output, $this->options));
	}

	public function stringDataProvider(){
		return [
			['1234567890'],
			['ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'],
			['#\\'],
			['茗荷'],
		];
	}

	/**
	 * @dataProvider stringDataProvider
	 */
	public function testDataCoverage($data){
		(new QRCode($data, new QRString))->getRawData();
	}

	/**
	 * @dataProvider stringDataProvider
	 */
	public function testTypeAndErrorcorrectlevelCoverage($data){
		foreach(QRConst::MAX_BITS as $type => $x){
			foreach(QRConst::RSBLOCK as $eclevel => $y){
				$this->options->typeNumber = $type;
				$this->options->errorCorrectLevel = $eclevel;
				$this->assertInstanceOf(QRCode::class, new QRCode($data, new QRString, $this->options));
			}
		}
	}

	/**
	 * @dataProvider stringDataProvider
	 */
	public function testTypeAutoOverride($data){
		$this->options->typeNumber = QRCode::TYPE_05;
		new QRCode($data, new QRString, $this->options);
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage No data given.
	 */
	public function testNoDataException(){
		new QRCode('', new QRString);
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage Invalid error correct level: 42
	 */
	public function testErrorCorrectLevelException(){
		$this->options->errorCorrectLevel = 42;
		new QRCode('foobar', new QRString, $this->options);
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage code length overflow. (261 > 72bit)
	 */
	public function testCodeLengthOverflowException(){
		$this->options->typeNumber = QRCode::TYPE_01;
		$this->options->errorCorrectLevel = QRCode::ERROR_CORRECT_LEVEL_H;
		(new QRCode('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', new QRString, $this->options))->getRawData();
	}

}
