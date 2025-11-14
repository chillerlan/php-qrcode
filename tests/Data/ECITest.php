<?php
/**
 * ECITest.php
 *
 * @created      12.03.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\{BitBuffer, ECICharset, Mode};
use chillerlan\QRCode\Data\{Byte, ECI, Hanzi, QRCodeDataException, QRData};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{DataProvider, Test};

/**
 * Tests the ECI class
 */
final class ECITest extends TestCase{

	private QRData $QRData;
	private int    $testCharset = ECICharset::GB18030;

	private const testData = '无可奈何燃花作香';

	protected function setUp():void{
		$this->QRData = new QRData(new QROptions);
	}

	/**
	 * @phpstan-return array{0: ECI, 1: Byte}
	 */
	private function getDataSegments():array{
		return [
			new ECI($this->testCharset),
			/** @phan-suppress-next-line PhanParamSuspiciousOrder */
			new Byte(mb_convert_encoding(self::testData, ECICharset::MB_ENCODINGS[$this->testCharset], mb_internal_encoding())),
		];
	}

	/**
	 * returns versions within the version breakpoints 1-9, 10-26 and 27-40
	 *
	 * @phpstan-return array<string, array{0: int}>
	 */
	public static function versionBreakpointProvider():array{
		return ['1-9' => [7], '10-26' => [15], '27-40' => [30]];
	}

	#[Test]
	#[DataProvider('versionBreakpointProvider')]
	public function decodeSegment(int $version):void{
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
		$this::assertSame(self::testData, (new ECI)->decodeSegment($bitBuffer, $options->version));
	}

	#[Test]
	public function invalidDataException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('invalid encoding id:');

		new ECI(-1);
	}

	/**
	 * since the ECI class only accepts integer values,
	 * we'll use this test to check for the upper end of the accepted input range
	 */
	#[Test]
	public function invalidDataOnEmptyException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('invalid encoding id:');

		new ECI(1000000);
	}

	/**
	 * @phpstan-return array<int, array{0: int, 1: int}>
	 */
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

	#[Test]
	#[DataProvider('eciCharsetIdProvider')]
	public function readWrite(int $id, int $lengthInBits):void{
		$bitBuffer = new BitBuffer;
		$eci       = (new ECI($id))->write($bitBuffer, 1);

		$this::assertSame($lengthInBits, $eci->getLengthInBits());
		$this::assertSame(Mode::ECI, $bitBuffer->read(4));
		$this::assertSame($id, $eci->parseValue($bitBuffer)->getID());
	}

	/**
	 * Tests if and exception is thrown when the ECI segment is followed by a mode that is not 8-bit byte
	 */
	#[Test]
	public function decodeECISegmentFollowedByInvalidModeException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('ECI designator followed by invalid mode:');

		$options          = new QROptions;
		$options->version = 5;

		/** @var \chillerlan\QRCode\Data\QRDataModeInterface[] $segments */
		$segments    = $this->getDataSegments();
		// follow the ECI segment by a non-8bit-byte segment
		$segments[1] = new Hanzi(self::testData);
		$bitBuffer   = (new QRData($options, $segments))->getBitBuffer();
		// verify the ECI mode indicator
		$this::assertSame(Mode::ECI, $bitBuffer->read(4));
		// throw
		(new ECI)->decodeSegment($bitBuffer, $options->version);
	}

	/**
	 * @phpstan-return array<string, array{0: int, 1: string}>
	 */
	public static function unknownEncodingDataProvider():array{
		return [
			'CP437'              => [0, "\x41\x42\x43"],
			'ISO_IEC_8859_1_GLI' => [1, "\x41\x42\x43"],
		];
	}

	/**
	 * Tests detection of an unknown character set
	 */
	#[Test]
	#[DataProvider('unknownEncodingDataProvider')]
	public function convertUnknownEncoding(int $id, string $data):void{
		$options          = new QROptions;
		$options->version = 5;

		$segments  = [new ECI($id), new Byte($data)];
		$bitBuffer = (new QRData($options, $segments))->getBitBuffer();
		$this::assertSame(Mode::ECI, $bitBuffer->read(4));
		$this::assertSame($data,(new ECI)->decodeSegment($bitBuffer, $options->version));
	}

}
