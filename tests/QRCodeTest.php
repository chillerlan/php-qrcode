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

use chillerlan\QRCode\Output\QRString;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QRConst;
use chillerlan\QRCode\QROptions;
use ReflectionClass;

class QRCodeTest extends \PHPUnit_Framework_TestCase{

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
		(new QRCode($data, $this->output))->getRawData();
	}

	/**
	 * @dataProvider stringDataProvider
	 */
	public function testTypeAndErrorcorrectlevelCoverage($data){
		foreach(QRConst::MAX_BITS as $type => $x){
			foreach(QRConst::RSBLOCK as $eclevel => $y){
				$this->options->typeNumber = $type;
				$this->options->errorCorrectLevel = $eclevel;
				$this->assertInstanceOf(QRCode::class, new QRCode($data, $this->output, $this->options));
			}
		}
	}

	/**
	 * @dataProvider stringDataProvider
	 */
	public function testTypeAutoOverride($data){
		$this->options->typeNumber = QRCode::TYPE_05;
		new QRCode($data, $this->output, $this->options);
	}


	public function getTypeNumberDataProvider(){
		return [
			[true,  QRCode::TYPE_05, 'foobar'],
			[false, QRCode::TYPE_05, 'foobar'],
			[true,  QRCode::TYPE_10, 'foobar'],
			[false, QRCode::TYPE_10, 'foobar'],
			[true,  QRCode::TYPE_05, '1234567890'],
			[false, QRCode::TYPE_05, '1234567890'],
			[true,  QRCode::TYPE_10, '1234567890'],
			[false, QRCode::TYPE_10, '1234567890'],
			[true,  QRCode::TYPE_05, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'],
			[false, QRCode::TYPE_05, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'],
			[true,  QRCode::TYPE_10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'],
			[false, QRCode::TYPE_10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'],
			[true,  QRCode::TYPE_05, '茗荷'],
			[false, QRCode::TYPE_05, '茗荷'],
			[true,  QRCode::TYPE_10, '茗荷'],
			[false, QRCode::TYPE_10, '茗荷'],
		];
	}

	/**
	 * @dataProvider getTypeNumberDataProvider
	 */
	public function testInternalGetTypeNumber($test, $type, $data){
		$method = $this->reflectionClass->getMethod('getMatrix');
		$method->setAccessible(true);
		$this->options->typeNumber = $type;

		for($i = 0; $i <= 7; $i++){
			$method->invokeArgs($this->reflectionClass->newInstanceArgs([$data, $this->output, $this->options]), [$test, $i]);
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

}
