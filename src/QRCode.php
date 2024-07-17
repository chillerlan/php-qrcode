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

use chillerlan\QRCode\Data\{
	AlphaNum, Byte, Kanji, MaskPatternTester, Number, QRCodeDataException, QRDataInterface, QRMatrix
};
use chillerlan\QRCode\Output\{
	QRCodeOutputException, QRFpdf, QRImage, QRImagick, QRMarkup, QROutputInterface, QRString
};
use chillerlan\Settings\SettingsContainerInterface;

use function call_user_func_array, class_exists, in_array, ord, strlen, strtolower, str_split;

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

	/**
	 * References to the keys of the following tables:
	 *
	 * @see \chillerlan\QRCode\Data\QRDataInterface::MAX_LENGTH
	 *
	 * @var int[]
	 */
	public const DATA_MODES = [
		self::DATA_NUMBER   => 0,
		self::DATA_ALPHANUM => 1,
		self::DATA_BYTE     => 2,
		self::DATA_KANJI    => 3,
	];

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
	 * @see \chillerlan\QRCode\Data\QRDataInterface::MAX_BITS
	 * @see \chillerlan\QRCode\Data\QRDataInterface::RSBLOCKS
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
		QRMarkup::class  => [
			self::OUTPUT_MARKUP_SVG,
			self::OUTPUT_MARKUP_HTML,
		],
		QRImage::class   => [
			self::OUTPUT_IMAGE_PNG,
			self::OUTPUT_IMAGE_GIF,
			self::OUTPUT_IMAGE_JPG,
		],
		QRString::class  => [
			self::OUTPUT_STRING_JSON,
			self::OUTPUT_STRING_TEXT,
		],
		QRImagick::class => [
			self::OUTPUT_IMAGICK,
		],
		QRFpdf::class    => [
			self::OUTPUT_FPDF
		]
	];

	/**
	 * Map of data mode => interface
	 *
	 * @var string[]
	 */
	protected const DATA_INTERFACES = [
		'number'   => Number::class,
		'alphanum' => AlphaNum::class,
		'kanji'    => Kanji::class,
		'byte'     => Byte::class,
	];

	/**
	 * The settings container
	 *
	 * @var \chillerlan\QRCode\QROptions|\chillerlan\Settings\SettingsContainerInterface
	 */
	protected SettingsContainerInterface $options;

	/**
	 * The selected data interface (Number, AlphaNum, Kanji, Byte)
	 */
	protected QRDataInterface $dataInterface;

	/**
	 * QRCode constructor.
	 *
	 * Sets the options instance, determines the current mb-encoding and sets it to UTF-8
	 */
	public function __construct(?SettingsContainerInterface $options = null){
		$this->options = $options ?? new QROptions;
	}

	/**
	 * Renders a QR Code for the given $data and QROptions
	 *
	 * @return mixed
	 */
	public function render(string $data, ?string $file = null){
		return $this->initOutputInterface($data)->dump($file);
	}

	/**
	 * Returns a QRMatrix object for the given $data and current QROptions
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function getMatrix(string $data):QRMatrix{

		if(empty($data)){
			throw new QRCodeDataException('QRCode::getMatrix() No data given.');
		}

		$this->dataInterface = $this->initDataInterface($data);

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
	 * returns a fresh QRDataInterface for the given $data
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function initDataInterface(string $data):QRDataInterface{

		// allow forcing the data mode
		// see https://github.com/chillerlan/php-qrcode/issues/39
		$interface = $this::DATA_INTERFACES[strtolower($this->options->dataModeOverride)] ?? null;

		if($interface !== null){
			return new $interface($this->options, $data);
		}

		foreach($this::DATA_INTERFACES as $mode => $dataInterface){

			if(call_user_func_array([$this, 'is'.$mode], [$data])){
				return new $dataInterface($this->options, $data);
			}

		}

		throw new QRCodeDataException('invalid data type'); // @codeCoverageIgnore
	}

	/**
	 * returns a fresh (built-in) QROutputInterface
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function initOutputInterface(string $data):QROutputInterface{

		if($this->options->outputType === $this::OUTPUT_CUSTOM && class_exists($this->options->outputInterface)){
			/** @phan-suppress-next-line PhanTypeExpectedObjectOrClassName */
			return new $this->options->outputInterface($this->options, $this->getMatrix($data));
		}

		foreach($this::OUTPUT_MODES as $outputInterface => $modes){

			if(in_array($this->options->outputType, $modes, true) && class_exists($outputInterface)){
				return new $outputInterface($this->options, $this->getMatrix($data));
			}

		}

		throw new QRCodeOutputException('invalid output type');
	}

	/**
	 * checks if a string qualifies as numeric
	 */
	public function isNumber(string $string):bool{
		return $this->checkString($string, QRDataInterface::CHAR_MAP_NUMBER);
	}

	/**
	 * checks if a string qualifies as alphanumeric
	 */
	public function isAlphaNum(string $string):bool{
		return $this->checkString($string, QRDataInterface::CHAR_MAP_ALPHANUM);
	}

	/**
	 * checks is a given $string matches the characters of a given $charmap, returns false on the first invalid occurence.
	 */
	protected function checkString(string $string, array $charmap):bool{

		foreach(str_split($string) as $chr){
			if(!isset($charmap[$chr])){
				return false;
			}
		}

		return true;
	}

	/**
	 * checks if a string qualifies as Kanji
	 */
	public function isKanji(string $string):bool{
		$i   = 0;
		$len = strlen($string);

		while($i + 1 < $len){
			$c = ((0xff & ord($string[$i])) << 8) | (0xff & ord($string[$i + 1]));

			if(!($c >= 0x8140 && $c <= 0x9FFC) && !($c >= 0xE040 && $c <= 0xEBBF)){
				return false;
			}

			$i += 2;
		}

		return $i >= $len;
	}

	/**
	 * a dummy
	 */
	public function isByte(string $data):bool{
		return $data !== '';
	}

}
