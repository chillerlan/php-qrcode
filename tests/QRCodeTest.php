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

use chillerlan\QRCode\{QRCode, QRCodeException, QROptions};
use chillerlan\QRCode\Common\ECICharset;
use chillerlan\QRCode\Output\{QRCodeOutputException, QRGdImagePNG};
use chillerlan\QRCodeTest\Traits\BuildDirTrait;
use PHPUnit\Framework\Attributes\Test;
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
	#[Test]
	public function initCustomOutputInterfaceNotExistsException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('invalid output class');

		$this->options->outputInterface = 'foo';

		$this->qrcode->setOptions($this->options)->render('test');
	}

	/**
	 * tests if an exception is thrown if the given output class does not implement QROutputInterface
	 */
	#[Test]
	public function initCustomOutputInterfaceNotImplementsException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('output class does not implement QROutputInterface');

		$this->options->outputInterface = stdClass::class;

		$this->qrcode->setOptions($this->options)->render('test');
	}

	/**
	 * Tests if an exception is thrown when trying to write a cache file to an invalid destination
	 */
	#[Test]
	public function saveException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('Cannot write data to cache file: /foo/bar.test');

		$this->options->cachefile = '/foo/bar.test';

		$this->qrcode->setOptions($this->options)->render('test');
	}

	/**
	 * Tests if a cache file is properly saved in the given path
	 */
	#[Test]
	public function renderToCacheFile():void{
		$fileSubPath = $this::buildDir.'/test.cache.svg';

		$this->options->cachefile    = $this->getBuildPath($fileSubPath);
		$this->options->outputBase64 = false;
		// create the cache file
		$data = $this->qrcode->setOptions($this->options)->render('test');

		$this::assertSame($data, $this->getBuildFileContent($fileSubPath));
	}

	/**
	 * Tests adding and decoding an ECI sequence
	 */
	#[Test]
	public function addEciSegment():void{
		$expected = '无可奈何燃花作香';

		$qrCode = (new QRCode([
			'outputBase64'    => false,
			'outputInterface' => QRGdImagePNG::class,
		]));

		$qrCode->addEciSegment(ECICharset::GB18030, $expected);

		$result = $qrCode->readFromBlob($qrCode->render());

		$this::assertSame($expected, $result->data);
	}

	#[Test]
	public function addEciSegmentInvalidCharsetException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('unable to add ECI segment');

		(new QRCode)->addEciSegment(666, 'nope');
	}

}
