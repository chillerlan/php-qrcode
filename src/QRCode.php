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
	MaskPatternTester, QRCodeDataException, QRDataInterface, QRMatrix
};
use chillerlan\QRCode\Output\{
	QRCodeOutputException, QRFpdf, QRImage, QRImagick, QRMarkup, QROutputInterface, QRString
};
use chillerlan\Settings\SettingsContainerInterface;

use function array_search, call_user_func_array, class_exists, in_array, min, ord, strlen;

/**
 * Turns a text string into a Model 2 QR Code
 *
 * @link https://github.com/kazuhikoarase/qrcode-generator/tree/master/php
 * @link http://www.qrcode.com/en/codes/model12.html
 * @link http://www.thonky.com/qr-code-tutorial/
 */
class QRCode{

	/**
	 * API constants
	 */
	public const OUTPUT_MARKUP_HTML = 'html';
	public const OUTPUT_MARKUP_SVG  = 'svg';
	public const OUTPUT_IMAGE_PNG   = 'png';
	public const OUTPUT_IMAGE_JPG   = 'jpg';
	public const OUTPUT_IMAGE_GIF   = 'gif';
	public const OUTPUT_STRING_JSON = 'json';
	public const OUTPUT_STRING_TEXT = 'text';
	public const OUTPUT_IMAGICK     = 'imagick';
	public const OUTPUT_FPDF        = 'fpdf';
	public const OUTPUT_CUSTOM      = 'custom';

	public const VERSION_AUTO       = -1;
	public const MASK_PATTERN_AUTO  = -1;

	public const ECC_L         = 0b01; // 7%.
	public const ECC_M         = 0b00; // 15%.
	public const ECC_Q         = 0b11; // 25%.
	public const ECC_H         = 0b10; // 30%.

	public const DATA_NUMBER   = 0b0001;
	public const DATA_ALPHANUM = 0b0010;
	public const DATA_BYTE     = 0b0100;
	public const DATA_KANJI    = 0b1000;

	public const ECC_MODES = [
		self::ECC_L => 0,
		self::ECC_M => 1,
		self::ECC_Q => 2,
		self::ECC_H => 3,
	];

	public const DATA_MODES = [
		self::DATA_NUMBER   => 0,
		self::DATA_ALPHANUM => 1,
		self::DATA_BYTE     => 2,
		self::DATA_KANJI    => 3,
	];

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
			self::OUTPUT_FPDF
		]
	];

	/**
	 * @var \chillerlan\QRCode\QROptions|\chillerlan\Settings\SettingsContainerInterface
	 */
	protected $options;

	/**
	 * @var \chillerlan\QRCode\Data\QRDataInterface
	 */
	protected $dataInterface;

	/**
	 * QRCode constructor.
	 *
	 * @param \chillerlan\Settings\SettingsContainerInterface|null $options
	 */
	public function __construct(SettingsContainerInterface $options = null){
		$this->options = $options ?? new QROptions;
	}

	/**
	 * Renders a QR Code for the given $data and QROptions
	 *
	 * @param string      $data
	 * @param string|null $file
	 *
	 * @return mixed
	 */
	public function render(string $data, string $file = null){
		return $this->initOutputInterface($data)->dump($file);
	}

	/**
	 * Returns a QRMatrix object for the given $data and current QROptions
	 *
	 * @param string $data
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function getMatrix(string $data):QRMatrix{

		if(empty($data)){
			throw new QRCodeDataException('QRCode::getMatrix() No data given.');
		}

		$this->dataInterface = $this->initDataInterface($data);

		$maskPattern = $this->options->maskPattern === $this::MASK_PATTERN_AUTO
			? $this->getBestMaskPattern()
			: $this->options->maskPattern;

		$matrix = $this->dataInterface->initMatrix($maskPattern);

		if((bool)$this->options->addQuietzone){
			$matrix->setQuietZone($this->options->quietzoneSize);
		}

		return $matrix;
	}

	/**
	 * shoves a QRMatrix through the MaskPatternTester to find the lowest penalty mask pattern
	 *
	 * @see \chillerlan\QRCode\Data\MaskPatternTester
	 *
	 * @return int
	 */
	protected function getBestMaskPattern():int{
		$penalties = [];

		for($pattern = 0; $pattern < 8; $pattern++){
			$tester = new MaskPatternTester($this->dataInterface->initMatrix($pattern, true));

			$penalties[$pattern] = $tester->testPattern();
		}

		return array_search(min($penalties), $penalties, true);
	}

	/**
	 * returns a fresh QRDataInterface for the given $data
	 *
	 * @param string                       $data
	 *
	 * @return \chillerlan\QRCode\Data\QRDataInterface
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function initDataInterface(string $data):QRDataInterface{
		$dataModes     = ['Number', 'AlphaNum', 'Kanji', 'Byte'];
		$dataNamespace = __NAMESPACE__.'\\Data\\';

		// allow forcing the data mode
		// see https://github.com/chillerlan/php-qrcode/issues/39
		if(in_array($this->options->dataMode, $dataModes, true)){
			$dataInterface = $dataNamespace.$this->options->dataMode;

			return new $dataInterface($this->options, $data);
		}

		foreach($dataModes as $mode){
			$dataInterface = $dataNamespace.$mode;

			if(call_user_func_array([$this, 'is'.$mode], [$data]) && class_exists($dataInterface)){
				return new $dataInterface($this->options, $data);
			}

		}

		throw new QRCodeDataException('invalid data type'); // @codeCoverageIgnore
	}

	/**
	 * returns a fresh (built-in) QROutputInterface
	 *
	 * @param string $data
	 *
	 * @return \chillerlan\QRCode\Output\QROutputInterface
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function initOutputInterface(string $data):QROutputInterface{

		if($this->options->outputType === $this::OUTPUT_CUSTOM && class_exists($this->options->outputInterface)){
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
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public function isNumber(string $string):bool{
		return $this->checkString($string, QRDataInterface::NUMBER_CHAR_MAP);
	}

	/**
	 * checks if a string qualifies as alphanumeric
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public function isAlphaNum(string $string):bool{
		return $this->checkString($string, QRDataInterface::ALPHANUM_CHAR_MAP);
	}

	/**
	 * checks is a given $string matches the characters of a given $charmap, returns false on the first invalid occurence.
	 *
	 * @param string $string
	 * @param array  $charmap
	 *
	 * @return bool
	 */
	protected function checkString(string $string, array $charmap):bool{
		$len = strlen($string);

		for($i = 0; $i < $len; $i++){
			if(!in_array($string[$i], $charmap, true)){
				return false;
			}
		}

		return true;
	}

	/**
	 * checks if a string qualifies as Kanji
	 *
	 * @param string $string
	 *
	 * @return bool
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
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	protected function isByte(string $data):bool{
		return !empty($data);
	}

}
