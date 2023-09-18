<?php
/**
 * Class QROutputTestAbstract
 *
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRCodeOutputException, QROutputInterface};
use PHPUnit\Framework\TestCase;

use function file_exists, mkdir;

/**
 * Test abstract for the several (built-in) output modules,
 * should also be used to test custom output modules
 */
abstract class QROutputTestAbstract extends TestCase{

	/** @var \chillerlan\QRCode\QROptions|\chillerlan\Settings\SettingsContainerInterface */
	protected QROptions         $options;
	protected QROutputInterface $outputInterface;
	protected QRMatrix          $matrix;
	protected string            $builddir = __DIR__.'/../../.build/output_test';
	protected string            $FQN;
	protected string            $type;

	/**
	 * Attempts to create a directory under /.build and instances several required objects
	 */
	protected function setUp():void{

		if(!file_exists($this->builddir)){
			mkdir($this->builddir, 0777, true);
		}

		$this->options             = new QROptions;
		$this->options->outputType = $this->type;
		$this->matrix              = (new QRCode($this->options))->addByteSegment('testdata')->getQRMatrix();
		$this->outputInterface     = new $this->FQN($this->options, $this->matrix);
	}

	/**
	 * Validate the instance of the interface
	 */
	public function testInstance():void{
		$this::assertInstanceOf(QROutputInterface::class, $this->outputInterface);
	}

	/**
	 * Tests if an exception is thrown when trying to write a cache file to an invalid destination
	 */
	public function testSaveException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('Cannot write data to cache file: /foo/bar.test');

		$this->outputInterface = new $this->FQN($this->options, $this->matrix);
		$this->outputInterface->dump('/foo/bar.test');
	}

	abstract public static function moduleValueProvider():array;

	/**
	 * @param mixed $value
	 * @param bool  $expected
	 *
	 * @dataProvider moduleValueProvider
	 */
	public function testValidateModuleValues($value, bool $expected):void{
		/** @noinspection PhpUndefinedMethodInspection */
		$this::assertSame($expected, $this->FQN::moduleValueIsValid($value));
	}

	/*
	 * additional, non-essential, potentially inaccurate coverage tests
	 */

	/**
	 * covers the module values settings
	 */
	abstract public function testSetModuleValues():void;

	/**
	 * coverage of the built-in output modules
	 */
	public function testRenderToCacheFile():void{
		$this->options->outputBase64 = false;
		$this->outputInterface       = new $this->FQN($this->options, $this->matrix);
		// create the cache file
		$file = $this->builddir.'/test.output.'.$this->type;
		$data = $this->outputInterface->dump($file);

		$this::assertSame($data, file_get_contents($file));
	}

}
