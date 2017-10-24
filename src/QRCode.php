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

use chillerlan\QRCode\Output\QROutputInterface;

/**
 * @link https://github.com/kazuhikoarase/qrcode-generator/tree/master/php
 * @link http://www.thonky.com/qr-code-tutorial/
 */
class QRCode{

	/**
	 * API constants
	 */
	const OUTPUT_STRING_TEXT = 'txt';
	const OUTPUT_STRING_JSON = 'json';

	const OUTPUT_MARKUP_HTML = 'html';
	const OUTPUT_MARKUP_SVG  = 'svg';
#	const OUTPUT_MARKUP_XML  = 'xml'; // anyone?

	const OUTPUT_IMAGE_PNG = 'png';
	const OUTPUT_IMAGE_JPG = 'jpg';
	const OUTPUT_IMAGE_GIF = 'gif';

	const ERROR_CORRECT_LEVEL_L = 1; // 7%.
	const ERROR_CORRECT_LEVEL_M = 0; // 15%.
	const ERROR_CORRECT_LEVEL_Q = 3; // 25%.
	const ERROR_CORRECT_LEVEL_H = 2; // 30%.

	// max bits @ ec level L:07 M:15 Q:25 H:30 %
	const TYPE_01 =  1; //  152  128  104   72
	const TYPE_02 =  2; //  272  224  176  128
	const TYPE_03 =  3; //  440  352  272  208
	const TYPE_04 =  4; //  640  512  384  288
	const TYPE_05 =  5; //  864  688  496  368
	const TYPE_06 =  6; // 1088  864  608  480
	const TYPE_07 =  7; // 1248  992  704  528
	const TYPE_08 =  8; // 1552 1232  880  688
	const TYPE_09 =  9; // 1856 1456 1056  800
	const TYPE_10 = 10; // 2192 1728 1232  976

	/**
	 * @var int
	 */
	protected $typeNumber;

	/**
	 * @var int
	 */
	protected $errorCorrectLevel;

	/**
	 * @var \chillerlan\QRCode\Output\QROutputInterface
	 */
	protected $qrOutputInterface;

	/**
	 * @var string
	 */
	protected $data;

	/**
	 * QRCode constructor.
	 *
	 * @param string                                      $data
	 * @param \chillerlan\QRCode\Output\QROutputInterface $output
	 * @param \chillerlan\QRCode\QROptions|null           $options
	 */
	public function __construct($data, QROutputInterface $output, QROptions $options = null){
		$this->qrOutputInterface = $output;

		$this->setData($data, $options);
	}

	/**
	 * @param string                            $data
	 * @param \chillerlan\QRCode\QROptions|null $options
	 *
	 * @return \chillerlan\QRCode\QRCode
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function setData(string $data, QROptions $options = null):QRCode {

		if(!$options instanceof QROptions){
			$options = new QROptions;
		}

		$this->data              = trim($data);
		$this->errorCorrectLevel = (int)$options->errorCorrectLevel;
		$this->typeNumber        = (int)$options->typeNumber;

		if(empty($this->data)){
			throw new QRCodeException('No data given.');
		}

		if(!in_array($this->errorCorrectLevel, range(0, 3), true)){
			throw new QRCodeException('Invalid error correct level: '.$this->errorCorrectLevel);
		}

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function output(){
		$this->qrOutputInterface->setMatrix($this->getRawData());

		return $this->qrOutputInterface->dump();
	}

	/**
	 * @return array
	 */
	public function getRawData():array {
		return (new QRDataGenerator($this->data, $this->typeNumber, $this->errorCorrectLevel))->getRawData();
	}

}
