<?php
/**
 * Class DataInterfaceTestAbstract
 *
 * @created      24.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Common\{EccLevel, MaskPattern, Mode, Version};
use chillerlan\QRCode\Data\{QRCodeDataException, QRData, QRDataModeInterface, QRMatrix};
use chillerlan\QRCode\QROptions;
use chillerlan\QRCodeTest\QRMaxLengthTrait;
use Exception, Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use function array_map, hex2bin, mb_strlen, mb_substr, sprintf, str_repeat, strlen, substr;

/**
 * The data interface test abstract
 */
abstract class DataInterfaceTestAbstract extends TestCase{
	use QRMaxLengthTrait;

	protected QRData              $QRData;
	protected QRDataModeInterface $dataMode;

	protected const testData = '';

	protected function setUp():void{
		$this->QRData   = new QRData(new QROptions);
		$this->dataMode = static::getDataModeInterface(static::testData);
	}

	abstract protected static function getDataModeInterface(string $data):QRDataModeInterface;

	/**
	 * @return int[][]
	 */
	public static function maskPatternProvider():array{
		return [[0], [1], [2], [3], [4], [5], [6], [7]];
	}

	/**
	 * Tests initializing the data matrix
	 */
	#[DataProvider('maskPatternProvider')]
	public function testInitMatrix(int $pattern):void{
		$maskPattern = new MaskPattern($pattern);

		$this->QRData->setData([$this->dataMode]);

		$matrix = $this->QRData->writeMatrix()->setFormatInfo($maskPattern)->mask($maskPattern);

		$this::assertInstanceOf(QRMatrix::class, $matrix);
		$this::assertSame($pattern, $matrix->getMaskPattern()->getPattern());
	}

	/**
	 * @phpstan-return array<int, array{0: string, 1: bool}>
	 */
	abstract public static function stringValidateProvider():array;

	/**
	 * Tests if a string is properly validated for the respective data mode
	 */
	#[DataProvider('stringValidateProvider')]
	public function testValidateString(string $string, bool $expected):void{
		$this::assertSame($expected, $this->dataMode::validateString($string));
	}

	/**
	 * Tests if a random binary string is properly validated as false
	 *
	 * @see https://github.com/chillerlan/php-qrcode/issues/182
	 */
	public function testBinaryStringInvalid():void{
		$this::assertFalse($this->dataMode::validateString(hex2bin('01015989f47dff8e852122117e04c90b9f15defc1c36477b1fe1')));
	}

	/**
	 * returns versions within the version breakpoints 1-9, 10-26 and 27-40
	 *
	 * @phpstan-return array<string, array{0: int}>
	 */
	public static function versionBreakpointProvider():array{
		return ['1-9' => [7], '10-26' => [15], '27-40' => [30]];
	}

	/**
	 * Tests decoding a data segment from a given BitBuffer
	 */
	#[DataProvider('versionBreakpointProvider')]
	public function testDecodeSegment(int $version):void{
		$options          = new QROptions;
		$options->version = $version;

		// invoke a QRData instance and write data
		$this->QRData = new QRData($options, [$this->dataMode]);
		// get the filled bitbuffer
		$bitBuffer = $this->QRData->getBitBuffer();
		// read the first 4 bits
		$this::assertSame($this->dataMode::DATAMODE, $bitBuffer->read(4));
		// decode the data
		$this::assertSame(static::testData, $this->dataMode::decodeSegment($bitBuffer, $options->version));
	}

	/**
	 * Generates test data for each data mode:
	 *
	 *   - version
	 *   - ECC level
	 *   - a string that contains the maximum amount of characters for the given mode
	 *   - a string that contains characters for the given mode and that exceeds the maximum length by one/two character(s)
	 *   - the maximum allowed character length
	 *
	 * @throws \Exception
	 */
	public static function maxLengthProvider():Generator{
		$eccLevels = array_map(fn(int $ecc):EccLevel => new EccLevel($ecc), [EccLevel::L, EccLevel::M, EccLevel::Q, EccLevel::H]);
		$str       = str_repeat(static::testData, 1000);

		$dataMode  = static::getDataModeInterface(static::testData)::DATAMODE;
		$mb        = ($dataMode === Mode::KANJI || $dataMode === Mode::HANZI);

		for($v = 1; $v <= 40; $v++){
			$version = new Version($v);

			foreach($eccLevels as $eccLevel){
				// maximum characters per ecc/mode
				$len  = static::getMaxLengthForMode($dataMode, $version, $eccLevel);
				// a string that contains the maximum amount of characters for the given mode
				$val  = ($mb) ? mb_substr($str, 0, $len) : substr($str, 0, $len);
				// same as above but character count exceeds
				// kanji/hanzi may have space for a single character, so we add 2 to make sure we exceed
				$val1 = ($mb) ? mb_substr($str, 0, ($len + 2)) : substr($str, 0, ($len + 1));
				// array key
				$key  = sprintf('version: %s%s (%s)', $version, $eccLevel, $len);

				if((($mb) ? mb_strlen($val) : strlen($val)) !== $len){
					throw new Exception('output length does not match');
				}

				yield $key => [$version, $eccLevel, $val, $val1, $len];
			}
		}

	}

	#[DataProvider('maxLengthProvider')]
	public function testMaxLength(Version $version, EccLevel $eccLevel, string $str):void{
		$options           = new QROptions;
		$options->version  = $version->getVersionNumber();
		$options->eccLevel = $eccLevel->getLevel();

		$this->dataMode    = static::getDataModeInterface($str);
		$this->QRData      = new QRData($options, [$this->dataMode]);

		$bitBuffer         = $this->QRData->getBitBuffer();

		$this::assertSame($this->dataMode::DATAMODE, $bitBuffer->read(4));
		$this::assertSame($str, $this->dataMode::decodeSegment($bitBuffer, $options->version));
	}

	/**
	 * Tests getting the minimum QR version for the given data
	 */
	#[DataProvider('maxLengthProvider')]
	public function testGetMinimumVersion(Version $version, EccLevel $eccLevel, string $str):void{
		$options           = new QROptions;
		$options->version  = Version::AUTO;
		$options->eccLevel = $eccLevel->getLevel();

		$this->dataMode    = static::getDataModeInterface($str);
		$this->QRData      = new QRData($options, [$this->dataMode]);

		$bitBuffer         = $this->QRData->getBitBuffer();

		$this::assertLessThanOrEqual($eccLevel->getMaxBitsForVersion($version), $this->QRData->estimateTotalBitLength());

		$minimumVersionNumber = $this->QRData->getMinimumVersion()->getVersionNumber();

		try{
			$this::assertSame($version->getVersionNumber(), $minimumVersionNumber);
		}
		catch(ExpectationFailedException){
			$this::assertSame(($version->getVersionNumber() + 1), $minimumVersionNumber, 'safety margin');
		}

		// verify the encoded data
		$this::assertSame($this->dataMode::DATAMODE, $bitBuffer->read(4));
		$this::assertSame($str, $this->dataMode::decodeSegment($bitBuffer, $minimumVersionNumber));
	}

	/**
	 * Tests if an exception is thrown on data overflow
	 */
	#[DataProvider('maxLengthProvider')]
	public function testMaxLengthOverflowException(Version $version, EccLevel $eccLevel, string $str, string $str1):void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('code length overflow');

		$options           = new QROptions;
		$options->version  = $version->getVersionNumber();
		$options->eccLevel = $eccLevel->getLevel();


		new QRData($options, [static::getDataModeInterface($str1)]);
	}

	/**
	 * Tests if an exception is thrown when the data exceeds the maximum version while auto-detecting
	 */
	public function testGetMinimumVersionException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('data exceeds');

		$this->QRData->setData([static::getDataModeInterface(str_repeat(static::testData, 1337))]);
	}

	/**
	 * Tests if an exception is thrown when an invalid character is encountered
	 */
	public function testInvalidDataException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('invalid data');

		static::getDataModeInterface('##');
	}

	/**
	 * Tests if an exception is thrown if the given string is empty
	 */
	public function testInvalidDataOnEmptyException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('invalid data');

		static::getDataModeInterface('');
	}

}
