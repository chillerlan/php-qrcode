<?php
/**
 * ECITest.php
 *
 * @created      12.03.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\{BitBuffer, ECICharset, Mode};
use chillerlan\QRCode\Data\{Byte, ECI, Number, QRCodeDataException, QRData, QRDataModeInterface};
use PHPUnit\Framework\TestCase;

/**
 * Tests the ECI class
 */
final class ECITest extends TestCase{

	protected QRData $QRData;
	protected string $testdata    = '无可奈何燃花作香';
	private int      $testCharset = ECICharset::GB18030;

	protected function setUp():void{
		$this->QRData = new QRData(new QROptions);
	}

	private function getDataSegments():array{
		return [
			new ECI($this->testCharset),
			new Byte(mb_convert_encoding($this->testdata, ECICharset::MB_ENCODINGS[$this->testCharset], mb_internal_encoding())),
		];
	}

	/** @inheritDoc */
	public function testDataModeInstance():void{
		$datamode = new ECI($this->testCharset);

		$this::assertInstanceOf(QRDataModeInterface::class, $datamode);
	}

	/**
	 * returns versions within the version breakpoints 1-9, 10-26 and 27-40
	 */
	public static function versionBreakpointProvider():array{
		return ['1-9' => [7], '10-26' => [15], '27-40' => [30]];
	}

	/**
	 * @inheritDoc
	 * @dataProvider versionBreakpointProvider
	 */
	public function testDecodeSegment(int $version):void{
		$options = new QROptions;
		$options->version = $version;

		/** @var \chillerlan\QRCode\Data\QRDataModeInterface[] $segments */
		$segments = $this->getDataSegments();

		// invoke a QRData instance and write data
		$this->QRData = new QRData($options, $segments);
		// get the filled bitbuffer
		$bitBuffer = $this->QRData->getBitBuffer();
		// read the first 4 bits
		$this::assertSame($segments[0]::DATAMODE, $bitBuffer->read(4));
		// decode the data
		$this::assertSame($this->testdata, ECI::decodeSegment($bitBuffer, $options->version));
	}

	/** @inheritDoc */
	public function testInvalidDataException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('invalid encoding id:');
		/** @phan-suppress-next-line PhanNoopNew */
		new ECI(-1);
	}

	/**
	 * since the ECI class only accepts integer values,
	 * we'll use this test to check for the upper end of the accepted input range
	 *
	 * @inheritDoc
	 */
	public function testInvalidDataOnEmptyException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('invalid encoding id:');
		/** @phan-suppress-next-line PhanNoopNew */
		new ECI(1000000);
	}

	public static function eciCharsetIdProvider():array{
		return [
			[     0,  8],
			[   127,  8],
			[   128, 16],
			[ 16383, 16],
			[ 16384, 24],
			[999999, 24],
		];
	}

	/**
	 * @dataProvider eciCharsetIdProvider
	 */
	public function testReadWrite(int $id, int $lengthInBits):void{
		$bitBuffer = new BitBuffer;
		$eci       = (new ECI($id))->write($bitBuffer, 1);

		$this::assertSame($lengthInBits, $eci->getLengthInBits());
		$this::assertSame(Mode::ECI, $bitBuffer->read(4));
		$this::assertSame($id, ECI::parseValue($bitBuffer)->getID());
	}

	/**
	 * Tests if and exception is thrown when the ECI segment is followed by a mode that is not 8-bit byte
	 */
	public function testDecodeECISegmentFollowedByInvalidModeException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('ECI designator followed by invalid mode:');

		$options          = new QROptions;
		$options->version = 5;

		/** @var \chillerlan\QRCode\Data\QRDataModeInterface[] $segments */
		$segments    = $this->getDataSegments();
		// follow the ECI segment by a non-8bit-byte segment
		$segments[1] = new Number('1');
		$bitBuffer   = (new QRData($options, $segments))->getBitBuffer();
		$this::assertSame(Mode::ECI, $bitBuffer->read(4));
		ECI::decodeSegment($bitBuffer, $options->version);
	}

	public function unknownEncodingDataProvider():array{
		return [
			'CP437'              => [0, "\x41\x42\x43"],
			'ISO_IEC_8859_1_GLI' => [1, "\x41\x42\x43"],
		];
	}

	/**
	 * Tests detection of an unknown character set
	 *
	 * @dataProvider unknownEncodingDataProvider
	 */
	public function testConvertUnknownEncoding(int $id, string $data):void{
		$options          = new QROptions;
		$options->version = 5;

		$segments  = [new ECI($id), new Byte($data)];
		$bitBuffer = (new QRData($options, $segments))->getBitBuffer();
		$this::assertSame(Mode::ECI, $bitBuffer->read(4));
		$this::assertSame($data, ECI::decodeSegment($bitBuffer, $options->version));
	}

}
