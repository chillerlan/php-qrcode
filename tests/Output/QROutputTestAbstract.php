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

use chillerlan\QRCodeTest\BuildDirTrait;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRCodeOutputException, QROutputInterface};
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test abstract for the several (built-in) output modules,
 * should also be used to test custom output modules
 */
abstract class QROutputTestAbstract extends TestCase{
	use BuildDirTrait;

	/** @var \chillerlan\QRCode\QROptions|\chillerlan\Settings\SettingsContainerInterface */
	protected QROptions         $options;
	protected QROutputInterface $outputInterface;
	protected QRMatrix          $matrix;

	protected string            $type;
	protected const buildDir = 'output-test';

	/**
	 * Attempts to create a directory under /.build and instances several required objects
	 */
	protected function setUp():void{
		$this->createBuildDir($this::buildDir);

		$this->options             = new QROptions;
		$this->options->outputType = $this->type;
		$this->matrix              = (new QRCode($this->options))->addByteSegment('testdata')->getQRMatrix();
		$this->outputInterface     = $this->getOutputInterface($this->options, $this->matrix);
	}

	abstract protected function getOutputInterface(QROptions $options, QRMatrix $matrix):QROutputInterface;

	/**
	 * Tests if an exception is thrown when trying to write a cache file to an invalid destination
	 */
	public function testSaveException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('Cannot write data to cache file: /foo/bar.test');

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
		$this::assertSame($expected, $this->outputInterface::moduleValueIsValid($value));
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
		$this->outputInterface       = $this->getOutputInterface($this->options, $this->matrix);
		// create the cache file
		$name        = (new ReflectionClass($this->outputInterface))->getShortName();
		$fileSubPath = $this::buildDir.'/test.output.'.$name;
		$data        = $this->outputInterface->dump($this->getBuildPath($fileSubPath));

		$this::assertSame($data, $this->getBuildFileContent($fileSubPath));
	}

}
