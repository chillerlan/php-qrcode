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
	QRCodeOutputException, QRImage, QRMarkup, QROutputInterface, QRString
};
use chillerlan\Traits\{
	ClassLoader, ContainerInterface
};

/**
 * Turns a text string into a Model 2 QR Code
 *
 * @link https://github.com/kazuhikoarase/qrcode-generator/tree/master/php
 * @link http://www.qrcode.com/en/codes/model12.html
 * @link http://www.thonky.com/qr-code-tutorial/
 */
class QRCode{
	use ClassLoader;

	/**
	 * API constants
	 */
	const OUTPUT_MARKUP_HTML  = 'html';
	const OUTPUT_MARKUP_SVG   = 'svg';
#	const OUTPUT_MARKUP_EPS   = 'eps';
#	const OUTPUT_MARKUP_XML   = 'xml'; // anyone?

	const OUTPUT_IMAGE_PNG    = 'png';
	const OUTPUT_IMAGE_JPG    = 'jpg';
	const OUTPUT_IMAGE_GIF    = 'gif';

	const OUTPUT_STRING_JSON  = 'json';
	const OUTPUT_STRING_TEXT  = 'text';

	const OUTPUT_CUSTOM       = 'custom';

	const VERSION_AUTO        = -1;
	const MASK_PATTERN_AUTO   = -1;

	const ECC_L         = 0b01; // 7%.
	const ECC_M         = 0b00; // 15%.
	const ECC_Q         = 0b11; // 25%.
	const ECC_H         = 0b10; // 30%.

	const DATA_NUMBER   = 0b0001;
	const DATA_ALPHANUM = 0b0010;
	const DATA_BYTE     = 0b0100;
	const DATA_KANJI    = 0b1000;

	const ECC_MODES = [
		self::ECC_L => 0,
		self::ECC_M => 1,
		self::ECC_Q => 2,
		self::ECC_H => 3,
	];

	const DATA_MODES = [
		self::DATA_NUMBER   => 0,
		self::DATA_ALPHANUM => 1,
		self::DATA_BYTE     => 2,
		self::DATA_KANJI    => 3,
	];

	const OUTPUT_MODES = [
		QRMarkup::class => [
			self::OUTPUT_MARKUP_SVG,
			self::OUTPUT_MARKUP_HTML,
#			self::OUTPUT_MARKUP_EPS,
		],
		QRImage::class => [
			self::OUTPUT_IMAGE_PNG,
			self::OUTPUT_IMAGE_GIF,
			self::OUTPUT_IMAGE_JPG,
		],
		QRString::class => [
			self::OUTPUT_STRING_JSON,
			self::OUTPUT_STRING_TEXT,
		]
	];

	/**
	 * @var \chillerlan\QRCode\QROptions
	 */
	protected $options;

	/**
	 * @var \chillerlan\QRCode\Data\QRDataInterface
	 */
	protected $dataInterface;

	/**
	 * QRCode constructor.
	 *
	 * @param \chillerlan\Traits\ContainerInterface|null $options
	 */
	public function __construct(ContainerInterface $options = null){
		mb_internal_encoding('UTF-8');

		$this->setOptions($options ?? new QROptions);
	}

	/**
	 * Sets the options, called internally by the constructor
	 *
	 * @param \chillerlan\Traits\ContainerInterface $options
	 *
	 * @return \chillerlan\QRCode\QRCode
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function setOptions(ContainerInterface $options):QRCode{

		if(!array_key_exists($options->eccLevel, $this::ECC_MODES)){
			throw new QRCodeException('Invalid error correct level: '.$options->eccLevel);
		}

		if(!is_array($options->imageTransparencyBG) || count($options->imageTransparencyBG) < 3){
			$options->imageTransparencyBG = [255, 255, 255];
		}

		$options->version = (int)$options->version;

		// clamp min/max version number
		$options->versionMin = (int)min($options->versionMin, $options->versionMax);
		$options->versionMax = (int)max($options->versionMin, $options->versionMax);

		$this->options = $options;

		return $this;
	}

	/**
	 * Renders a QR Code for the given $data and QROptions
	 *
	 * @param string $data
	 *
	 * @return mixed
	 */
	public function render(string $data){
		return $this->initOutputInterface($data)->dump();
	}

	/**
	 * Returns a QRMatrix object for the given $data and current QROptions
	 *
	 * @param string $data
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function getMatrix(string $data):QRMatrix {
		// https://github.com/chillerlan/php-qrcode/pull/15
		// NOTE: input sanitization should be done outside
		// $data = trim($data);

		if(empty($data)){
			throw new QRCodeDataException('QRCode::getMatrix() No data given.');
		}

		$this->dataInterface = $this->initDataInterface($data);

		$maskPattern = $this->options->maskPattern === $this::MASK_PATTERN_AUTO
			? $this->getBestMaskPattern()
			: min(7, max(0, (int)$this->options->maskPattern));

		$matrix = $this
			->dataInterface
			->initMatrix($maskPattern)
		;

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

		for($testPattern = 0; $testPattern < 8; $testPattern++){
			$matrix = $this
				->dataInterface
				->initMatrix($testPattern, true);

			$penalties[$testPattern] = (new MaskPatternTester($matrix))->testPattern();
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

		$DATA_MODES = [
			Number::class   => 'Number',
			AlphaNum::class => 'AlphaNum',
			Kanji::class    => 'Kanji',
			Byte::class     => 'Byte',
		];

		foreach($DATA_MODES as $dataInterface => $mode){

			if(call_user_func_array([$this, 'is'.$mode], [$data]) === true){
				return $this->loadClass($dataInterface, QRDataInterface::class, $this->options, $data);
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

		if($this->options->outputType === $this::OUTPUT_CUSTOM && $this->options->outputInterface !== null){
			return $this->loadClass($this->options->outputInterface, QROutputInterface::class, $this->options, $this->getMatrix($data));
		}

		foreach($this::OUTPUT_MODES as $outputInterface => $modes){

			if(in_array($this->options->outputType, $modes, true)){
				return $this->loadClass($outputInterface, QROutputInterface::class, $this->options, $this->getMatrix($data));
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
	public function isNumber(string $string):bool {
		return $this->checkString($string, Number::CHAR_MAP);
	}

	/**
	 * checks if a string qualifies as alphanumeric
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public function isAlphaNum(string $string):bool {
		return $this->checkString($string, AlphaNum::CHAR_MAP);
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
	public function isKanji(string $string):bool {
		$i   = 0;
		$len = strlen($string);

		while($i + 1 < $len){
			$c = ((0xff&ord($string[$i])) << 8)|(0xff&ord($string[$i + 1]));

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
