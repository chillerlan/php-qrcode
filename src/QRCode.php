<?php
/**
 * Class QRCode
 *
 * @filesource   QRCode.php
 * @created      26.11.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

use chillerlan\QRCode\Data\{AlphaNum, Byte, ECI, Kanji, MaskPatternTester, Number, QRData, QRCodeDataException, QRMatrix};
use chillerlan\QRCode\Output\{
	QRCodeOutputException, QRFpdf, QRImage, QRImagick, QRMarkup, QROutputInterface, QRString
};
use chillerlan\Settings\SettingsContainerInterface;

use function class_exists, in_array;

/**
 * Turns a text string into a Model 2 QR Code
 *
 * @see https://github.com/kazuhikoarase/qrcode-generator/tree/master/php
 * @see http://www.qrcode.com/en/codes/model12.html
 * @see https://www.swisseduc.ch/informatik/theoretische_informatik/qr_codes/docs/qr_standard.pdf
 * @see https://en.wikipedia.org/wiki/QR_code
 * @see http://www.thonky.com/qr-code-tutorial/
 */
class QRCode{

	/** @var int */
	public const VERSION_AUTO       = -1;
	/** @var int */
	public const MASK_PATTERN_AUTO  = -1;

	// ISO/IEC 18004:2000 Table 2

	/** @var int */
	public const DATA_NUMBER   = 0b0001;
	/** @var int */
	public const DATA_ALPHANUM = 0b0010;
	/** @var int */
	public const DATA_BYTE     = 0b0100;
	/** @var int */
	public const DATA_KANJI    = 0b1000;
	/** @var int */
	public const DATA_ECI      = 0b0111;

	// ISO/IEC 18004:2000 Tables 12, 25

	/** @var int */
	public const ECC_L = 0b01; // 7%.
	/** @var int */
	public const ECC_M = 0b00; // 15%.
	/** @var int */
	public const ECC_Q = 0b11; // 25%.
	/** @var int */
	public const ECC_H = 0b10; // 30%.

	/**
	 * References to the keys of the following tables:
	 *
	 * @see \chillerlan\QRCode\Data\QRData::MAX_BITS
	 * @see \chillerlan\QRCode\Data\QRData::RSBLOCKS
	 * @see \chillerlan\QRCode\Data\QRMatrix::formatPattern
	 *
	 * @var int[]
	 */
	public const ECC_MODES = [
		self::ECC_L => 0,
		self::ECC_M => 1,
		self::ECC_Q => 2,
		self::ECC_H => 3,
	];

	/** @var string */
	public const OUTPUT_MARKUP_HTML = 'html';
	/** @var string */
	public const OUTPUT_MARKUP_SVG  = 'svg';
	/** @var string */
	public const OUTPUT_IMAGE_PNG   = 'png';
	/** @var string */
	public const OUTPUT_IMAGE_JPG   = 'jpg';
	/** @var string */
	public const OUTPUT_IMAGE_GIF   = 'gif';
	/** @var string */
	public const OUTPUT_STRING_JSON = 'json';
	/** @var string */
	public const OUTPUT_STRING_TEXT = 'text';
	/** @var string */
	public const OUTPUT_IMAGICK     = 'imagick';
	/** @var string */
	public const OUTPUT_FPDF        = 'fpdf';
	/** @var string */
	public const OUTPUT_CUSTOM      = 'custom';

	/**
	 * Map of built-in output modules => capabilities
	 *
	 * @var string[][]
	 */
	public const OUTPUT_MODES = [
		QRMarkup::class => [
			self::OUTPUT_MARKUP_SVG,
			self::OUTPUT_MARKUP_HTML,
		],
		QRImage::class => [
			self::OUTPUT_IMAGE_PNG,
			self::OUTPUT_IMAGE_GIF,
			self::OUTPUT_IMAGE_JPG,
		],
		QRString::class => [
			self::OUTPUT_STRING_JSON,
			self::OUTPUT_STRING_TEXT,
		],
		QRImagick::class => [
			self::OUTPUT_IMAGICK,
		],
		QRFpdf::class => [
			self::OUTPUT_FPDF,
		],
	];

	/**
	 * Map of data mode => interface (detection order)
	 *
	 * @var string[]
	 */
	protected const DATA_INTERFACES = [
		self::DATA_NUMBER   => Number::class,
		self::DATA_ALPHANUM => AlphaNum::class,
		self::DATA_KANJI    => Kanji::class,
		self::DATA_BYTE     => Byte::class,
	];

	/**
	 * A collection of one or more data segments of [classname, data] to write
	 *
	 * @see \chillerlan\QRCode\Data\QRDataModeInterface
	 *
	 * @var string[][]|int[][]
	 */
	protected array $dataSegments = [];

	/**
	 * The settings container
	 *
	 * @var \chillerlan\QRCode\QROptions|\chillerlan\Settings\SettingsContainerInterface
	 */
	protected SettingsContainerInterface $options;

	/**
	 * The selected data interface (Number, AlphaNum, Kanji, Byte)
	 */
	protected QRData $dataInterface;

	/**
	 * QRCode constructor.
	 *
	 * Sets the options instance, determines the current mb-encoding and sets it to UTF-8
	 */
	public function __construct(SettingsContainerInterface $options = null){
		$this->options = $options ?? new QROptions;
	}

	/**
	 * Renders a QR Code for the given $data and QROptions
	 *
	 * @return mixed
	 */
	public function render(string $data = null, string $file = null){

		if($data !== null){
			/** @var \chillerlan\QRCode\Data\QRDataModeInterface $dataInterface */
			foreach($this::DATA_INTERFACES as $dataInterface){

				if($dataInterface::validateString($data)){
					$this->addSegment($data, $dataInterface);

					break;
				}

			}

		}

		return $this->initOutputInterface()->dump($file);
	}



	/**
	 * Returns a QRMatrix object for the given $data and current QROptions
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function getMatrix():QRMatrix{

		if(empty($this->dataSegments)){
			throw new QRCodeDataException('QRCode::getMatrix() No data given.');
		}

		$this->dataInterface = new QRData($this->options, $this->dataSegments);

		$maskPattern = $this->options->maskPattern === $this::MASK_PATTERN_AUTO
			? (new MaskPatternTester($this->dataInterface))->getBestMaskPattern()
			: $this->options->maskPattern;

		$matrix = $this->dataInterface->initMatrix($maskPattern);

		if((bool)$this->options->addQuietzone){
			$matrix->setQuietZone($this->options->quietzoneSize);
		}

		return $matrix;
	}

	/**
	 * returns a fresh (built-in) QROutputInterface
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function initOutputInterface():QROutputInterface{

		if($this->options->outputType === $this::OUTPUT_CUSTOM && class_exists($this->options->outputInterface)){
			return new $this->options->outputInterface($this->options, $this->getMatrix());
		}

		foreach($this::OUTPUT_MODES as $outputInterface => $modes){

			if(in_array($this->options->outputType, $modes, true) && class_exists($outputInterface)){
				return new $outputInterface($this->options, $this->getMatrix());
			}

		}

		throw new QRCodeOutputException('invalid output type');
	}

	/**
	 * checks if a string qualifies as numeric (convenience method)
	 *
	 * @see Number::validateString()
	 */
	public function isNumber(string $string):bool{
		return Number::validateString($string);
	}

	/**
	 * checks if a string qualifies as alphanumeric (convenience method)
	 *
	 * @see AlphaNum::validateString()
	 */
	public function isAlphaNum(string $string):bool{
		return AlphaNum::validateString($string);
	}

	/**
	 * checks if a string qualifies as Kanji (convenience method)
	 *
	 * @see Kanji::validateString()
	 */
	public function isKanji(string $string):bool{
		return Kanji::validateString($string);
	}

	/**
	 * a dummy (convenience method)
	 *
	 * @see Byte::validateString()
	 */
	public function isByte(string $string):bool{
		return Byte::validateString($string);
	}

	/**
	 * @param string|int $data
	 * @param string     $classname
	 *
	 * @return void
	 */
	protected function addSegment($data, string $classname):void{
		$this->dataSegments[] = [$classname, $data];
	}

	public function addNumberSegment(string $data):QRCode{
		$this->addSegment($data, Number::class);

		return $this;
	}

	public function addAlphaNumSegment(string $data):QRCode{
		$this->addSegment($data, AlphaNum::class);

		return $this;
	}

	public function addKanjiSegment(string $data):QRCode{
		$this->addSegment($data, Kanji::class);

		return $this;
	}

	public function addByteSegment(string $data):QRCode{
		$this->addSegment($data, Byte::class);

		return $this;
	}

	public function addEciDesignator(int $encoding):QRCode{
		$this->addSegment($encoding, ECI::class);

		return $this;
	}
}
