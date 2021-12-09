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

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\MaskPattern;
use chillerlan\QRCode\Data\{Byte, QRData, QRMatrix};
use chillerlan\QRCode\Output\{QRCodeOutputException, QROutputInterface};
use PHPUnit\Framework\TestCase;

use function file_exists, in_array, mkdir;

use const PHP_OS_FAMILY, PHP_VERSION_ID;

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

	/**
	 * Attempts to create a directory under /.build and instances several required objects
	 */
	protected function setUp():void{

		if(!file_exists($this->builddir)){
			mkdir($this->builddir, 0777, true);
		}

		$this->options         = new QROptions;
		$this->matrix          = (new QRData($this->options, [new Byte('testdata')]))
			->writeMatrix(new MaskPattern(MaskPattern::PATTERN_010));
		$this->outputInterface = $this->getOutputInterface($this->options);
	}

	/**
	 * Returns a QROutputInterface instance with the given options and using $this->matrix
	 */
	abstract protected function getOutputInterface(QROptions $options):QROutputInterface;

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

		$this->options->cachefile = '/foo/bar.test';
		$this->outputInterface = $this->getOutputInterface($this->options);
		$this->outputInterface->dump();
	}

	/**
	 * covers the module values settings
	 */
	abstract public function testSetModuleValues():void;

	/*
	 * additional, non-essential, potentially inaccurate coverage tests
	 */

	/**
	 * @see testStringOutput()
	 * @return string[][]
	 */
	abstract public function types():array;

	/**
	 * coverage of the built-in output modules
	 *
	 * @dataProvider types
	 */
	public function testStringOutput(string $type):void{
		$this->options->outputType  = $type;
		$this->options->cachefile   = $this->builddir.'/test.'.$type;
		$this->options->imageBase64 = false;
		$this->outputInterface      = $this->getOutputInterface($this->options);
		$data                       = $this->outputInterface->dump(); // creates the cache file

		$this::assertSame($data, file_get_contents($this->options->cachefile));
	}

	/**
	 * covers the built-in output modules, tests against pre-rendered data
	 *
	 * @dataProvider types
	 */
	public function testRenderImage(string $type):void{

		// may fail on CI, different PHP (platform) versions produce different output
		// the samples were generated on php-7.4.3-Win32-vc15-x64
		if(
			(PHP_OS_FAMILY !== 'Windows' || PHP_VERSION_ID >= 80100)
			&& in_array($type, [QRCode::OUTPUT_IMAGE_JPG, QRCode::OUTPUT_IMAGICK, QRCode::OUTPUT_MARKUP_SVG])
		){
			$this::markTestSkipped('may fail on CI');
		}

		$this->options->outputType = $type;

		$this::assertSame(
			trim(file_get_contents(__DIR__.'/../samples/'.$type)),
			trim((new QRCode($this->options))->render('test'))
		);
	}

}
