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
use function file_get_contents;

/**
 * Tests basic functions of the QRCode class
 */
final class QRCodeTest extends TestCase{

	private QRCode    $qrcode;
	private QROptions $options;
	private string    $builddir = __DIR__.'/../.build/output_test';

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
		$this->options->cachefile    = $this->builddir.'/test.cache.svg';
		$this->options->outputBase64 = false;
		// create the cache file
		$data = $this->qrcode->setOptions($this->options)->render('test');

		$this::assertSame($data, file_get_contents($this->options->cachefile));
	}

}
