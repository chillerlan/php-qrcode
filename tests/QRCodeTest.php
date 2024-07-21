<?php
/**
 * Class QRCodeTest
 *
 * @created      17.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\{QROptions, QRCode};
use chillerlan\QRCode\Output\QRCodeOutputException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests basic functions of the QRCode class
 */
final class QRCodeTest extends TestCase{
	use BuildDirTrait;

	private QRCode    $qrcode;
	private QROptions $options;

	private const buildDir = 'output-test';

	/**
	 * invoke test instances
	 */
	protected function setUp():void{
		$this->createBuildDir($this::buildDir);

		$this->qrcode  = new QRCode;
		$this->options = new QROptions;
	}

	/**
	 * tests if an exception is thrown if the given output class does not exist
	 */
	public function testInitCustomOutputInterfaceNotExistsException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('invalid output class');

		$this->options->outputInterface = 'foo';

		$this->qrcode->setOptions($this->options)->render('test');
	}

	/**
	 * tests if an exception is thrown if the given output class does not implement QROutputInterface
	 */
	public function testInitCustomOutputInterfaceNotImplementsException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('output class does not implement QROutputInterface');

		$this->options->outputInterface = stdClass::class;

		$this->qrcode->setOptions($this->options)->render('test');
	}

	/**
	 * Tests if an exception is thrown when trying to write a cache file to an invalid destination
	 */
	public function testSaveException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('Cannot write data to cache file: /foo/bar.test');

		$this->options->cachefile = '/foo/bar.test';

		$this->qrcode->setOptions($this->options)->render('test');
	}

	/**
	 * Tests if a cache file is properly saved in the given path
	 */
	public function testRenderToCacheFile():void{
		$fileSubPath = $this::buildDir.'/test.cache.svg';

		$this->options->cachefile    = $this->getBuildPath($fileSubPath);
		$this->options->outputBase64 = false;
		// create the cache file
		$data = $this->qrcode->setOptions($this->options)->render('test');

		$this::assertSame($data, $this->getBuildFileContent($fileSubPath));
	}

}
