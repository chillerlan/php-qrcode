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
use chillerlan\Traits\ClassLoader;

/**
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
#	const OUTPUT_MARKUP_XML   = 'xml'; // anyone?

	const OUTPUT_IMAGE_PNG    = 'png';
	const OUTPUT_IMAGE_JPG    = 'jpg';
	const OUTPUT_IMAGE_GIF    = 'gif';

	const OUTPUT_STRING_JSON  = 'json';
	const OUTPUT_STRING_TEXT  = 'txt';

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
		QRCode::ECC_L => 0,
		QRCode::ECC_M => 1,
		QRCode::ECC_Q => 2,
		QRCode::ECC_H => 3,
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
	 * @param \chillerlan\QRCode\QROptions|null $options
	 */
	public function __construct(QROptions $options = null){
		mb_internal_encoding('UTF-8');

		$this->setOptions($options ?? new QROptions);
	}

	/**
	 * @param \chillerlan\QRCode\QROptions $options
	 *
	 * @return \chillerlan\QRCode\QRCode
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function setOptions(QROptions $options):QRCode{

		if(!array_key_exists(QRCode::ECC_MODES[$options->eccLevel], QRCode::ECC_MODES)){
			throw new QRCodeException('Invalid error correct level: '.$options->eccLevel);
		}

		$options->version = (int)$options->version;

		// clamp min/max version number
		$options->versionMin = (int)min($options->versionMin, $options->versionMax);
		$options->versionMax = (int)max($options->versionMin, $options->versionMax);

		$this->options = $options;

		return $this;
	}

	/**
	 * @param string $data
	 *
	 * @return mixed
	 */
	public function render(string $data){
		return $this->initOutputInterface($data)->dump();
	}

	/**
	 * @param string $data
	 *
	 * @return \chillerlan\QRCode\Data\QRMatrix
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function getMatrix(string $data):QRMatrix {
		$data = trim($data);

		if(empty($data)){
			throw new QRCodeDataException('QRCode::getMatrix() No data given.');
		}

		$this->dataInterface = $this->initDataInterface($data);

		$maskPattern = $this->options->maskPattern === self::MASK_PATTERN_AUTO
			? $this->getBestMaskPattern()
			: max(7, min(0, (int)$this->options->maskPattern));

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
	 * @return int
	 */
	protected function getBestMaskPattern():int{
		$penalties = [];

		$tester = new MaskPatternTester;

		for($testPattern = 0; $testPattern < 8; $testPattern++){
			$matrix = $this
				->dataInterface
				->initMatrix($testPattern, true);

			$tester->setMatrix($matrix);

			$penalties[$testPattern] = $tester->testPattern();
		}

		return array_search(min($penalties), $penalties, true);
	}

	/**
	 * @param string                       $data
	 *
	 * @return \chillerlan\QRCode\Data\QRDataInterface
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	protected function initDataInterface(string $data):QRDataInterface{

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

		throw new QRCodeDataException('invalid data type');
	}

	/**
	 * @param string $data
	 *
	 * @return \chillerlan\QRCode\Output\QROutputInterface
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function initOutputInterface(string $data):QROutputInterface{

		foreach(self::OUTPUT_MODES as $outputInterface => $modes){

			if(in_array($this->options->outputType, $modes, true)){
				return $this->loadClass($outputInterface, QROutputInterface::class, $this->options, $this->getMatrix($data));
			}

		}

		throw new QRCodeOutputException('invalid output type');
	}

	/**
	 * @param string $string
	 *
	 * @return bool
	 */
	public function isNumber(string $string):bool {
		$len = strlen($string);

		for($i = 0; $i < $len; $i++){
			$c = ord($string[$i]);

			if(!(ord('0') <= $c && $c <= ord('9'))){
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string $string
	 *
	 * @return bool
	 */
	public function isAlphaNum(string $string):bool {
		$len = strlen($string);

		for($i = 0; $i < $len; $i++){
			$c = ord($string[$i]);

			if(
				   !(ord('0') <= $c && $c <= ord('9'))
				&& !(ord('A') <= $c && $c <= ord('Z'))
				&& strpos(' $%*+-./:', $string[$i]) === false
			){
				return false;
			}
		}

		return true;
	}

	/**
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

		return !($i < $len);
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
