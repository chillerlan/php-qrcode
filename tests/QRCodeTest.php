<?php
/**
 * Class QRCodeTest
 *
 * @filesource   QRCodeTest.php
 * @created      17.11.2017
 * @package      chillerlan\QRCodeTest
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\{QROptions, QRCode};
use chillerlan\QRCode\Data\{AlphaNum, Byte, Kanji, Number, QRCodeDataException};
use chillerlan\QRCode\Output\QRCodeOutputException;
use PHPUnit\Framework\TestCase;

use function random_bytes;

/**
 * Tests basic functions of the QRCode class
 */
class QRCodeTest extends TestCase{

	/** @internal  */
	protected QRCode $qrcode;
	/** @internal  */
	protected QROptions $options;

	/**
	 * invoke test instances
	 *
	 * @internal
	 */
	protected function setUp():void{
		$this->qrcode  = new QRCode;
		$this->options = new QROptions;
	}

	/**
	 * isNumber() should pass on any number and fail on anything else
	 */
	public function testIsNumber():void{
		$this::assertTrue($this->qrcode->isNumber('0123456789'));

		$this::assertFalse($this->qrcode->isNumber('ABC123'));
	}

	/**
	 * isAlphaNum() should pass on the 45 defined characters and fail on anything else (e.g. lowercase)
	 */
	public function testIsAlphaNum():void{
		$this::assertTrue($this->qrcode->isAlphaNum('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'));

		$this::assertFalse($this->qrcode->isAlphaNum('abc'));
	}

	/**
	 * isKanji() should pass on Kanji/SJIS characters and fail on everything else
	 */
	public function testIsKanji():void{
		$this::assertTrue($this->qrcode->isKanji('茗荷'));

		$this::assertFalse($this->qrcode->isKanji('Ã'));
		$this::assertFalse($this->qrcode->isKanji('ABC'));
		$this::assertFalse($this->qrcode->isKanji('123'));
	}

	/**
	 * isByte() passses any binary string and only fails on empty strings
	 */
	public function testIsByte():void{
		$this::assertTrue($this->qrcode->isByte("\x01\x02\x03"));
		$this::assertTrue($this->qrcode->isByte('            ')); // not empty!
		$this::assertTrue($this->qrcode->isByte('0'));

		$this::assertFalse($this->qrcode->isByte(''));
	}

	/**
	 * tests if an exception is thrown when an invalid (built-in) output type is specified
	 */
	public function testInitDataInterfaceException():void{
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('invalid output type');

		$this->options->outputType = 'foo';

		(new QRCode($this->options))->render('test');
	}

	/**
	 * tests if an exception is thrown when trying to call getMatrix() without data (empty string, no data set)
	 */
	public function testGetMatrixException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('QRCode::getMatrix() No data given.');

		$this->qrcode->getMatrix('');
	}

	/**
	 * test whether stings are trimmed (they are not) - i'm still torn on that (see isByte)
	 */
	public function testAvoidTrimming():void{
		$m1 = $this->qrcode->getMatrix('hello')->matrix();
		$m2 = $this->qrcode->getMatrix('hello ')->matrix(); // added space

		$this::assertNotSame($m1, $m2);
	}

	/**
	 * tests if the data mode is overriden if QROptions::$dataModeOverride is set to a valid value
	 *
	 * @see https://github.com/chillerlan/php-qrcode/issues/39
	 */
	public function testDataModeOverride():void{

		// no (or invalid) value set - auto detection
		$this->options->dataModeOverride = 'foo';
		$this->qrcode = new QRCode;

		$this::assertInstanceOf(Number::class, $this->qrcode->initDataInterface('123'));
		$this::assertInstanceOf(AlphaNum::class, $this->qrcode->initDataInterface('ABC123'));
		$this::assertInstanceOf(Byte::class, $this->qrcode->initDataInterface(random_bytes(32)));
		$this::assertInstanceOf(Kanji::class, $this->qrcode->initDataInterface('茗荷'));

		// data mode set: force the given data mode
		$this->options->dataModeOverride = 'Byte';
		$this->qrcode = new QRCode($this->options);

		$this::assertInstanceOf(Byte::class, $this->qrcode->initDataInterface('123'));
		$this::assertInstanceOf(Byte::class, $this->qrcode->initDataInterface('ABC123'));
		$this::assertInstanceOf(Byte::class, $this->qrcode->initDataInterface(random_bytes(32)));
		$this::assertInstanceOf(Byte::class, $this->qrcode->initDataInterface('茗荷'));
	}

	/**
	 * tests if an exception is thrown when an invalid character occurs when forcing a data mode other than Byte
	 */
	public function testDataModeOverrideError():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('illegal char:');

		$this->options->dataModeOverride = 'AlphaNum';

		(new QRCode($this->options))->initDataInterface(random_bytes(32));
	}

}
