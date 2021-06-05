<?php
/**
 * Class QRCode
 *
 * @created      26.11.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

use chillerlan\QRCode\Common\{ECICharset, MaskPattern, MaskPatternTester, Mode};
use chillerlan\QRCode\Data\{AlphaNum, Byte, ECI, Kanji, Number, QRData, QRCodeDataException, QRDataModeInterface, QRMatrix};
use chillerlan\QRCode\Output\{QRCodeOutputException, QRFpdf, QRImage, QRImagick, QRMarkup, QROutputInterface, QRString};
use chillerlan\Settings\SettingsContainerInterface;
use function class_exists, in_array, mb_convert_encoding, mb_detect_encoding;

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
	 * A collection of one or more data segments of [classname, data] to write
	 *
	 * @see \chillerlan\QRCode\Data\QRDataModeInterface
	 *
	 * @var \chillerlan\QRCode\Data\QRDataModeInterface[]
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
			foreach(Mode::DATA_INTERFACES as $dataInterface){

				if($dataInterface::validateString($data)){
					$this->addSegment(new $dataInterface($data));

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
			: new MaskPattern($this->options->maskPattern);

		$matrix = $this->dataInterface->writeMatrix($maskPattern);

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
	 */
	public function isNumber(string $string):bool{
		return Number::validateString($string);
	}

	/**
	 * checks if a string qualifies as alphanumeric (convenience method)
	 */
	public function isAlphaNum(string $string):bool{
		return AlphaNum::validateString($string);
	}

	/**
	 * checks if a string qualifies as Kanji (convenience method)
	 */
	public function isKanji(string $string):bool{
		return Kanji::validateString($string);
	}

	/**
	 * a dummy (convenience method)
	 */
	public function isByte(string $string):bool{
		return Byte::validateString($string);
	}

	/**
	 * ISO/IEC 18004:2000 8.3.6 - Mixing modes
	 * ISO/IEC 18004:2000 Annex H - Optimisation of bit stream length
	 */
	protected function addSegment(QRDataModeInterface $segment):void{
		$this->dataSegments[] = $segment;
	}

	/**
	 * ISO/IEC 18004:2000 8.3.2 - Numeric Mode
	 */
	public function addNumberSegment(string $data):QRCode{
		$this->addSegment(new Number($data));

		return $this;
	}

	/**
	 * ISO/IEC 18004:2000 8.3.3 - Alphanumeric Mode
	 */
	public function addAlphaNumSegment(string $data):QRCode{
		$this->addSegment(new AlphaNum($data));

		return $this;
	}

	/**
	 * ISO/IEC 18004:2000 8.3.5 - Kanji Mode
	 */
	public function addKanjiSegment(string $data):QRCode{
		$this->addSegment(new Kanji($data));

		return $this;
	}

	/**
	 * ISO/IEC 18004:2000 8.3.4 - 8-bit Byte Mode
	 */
	public function addByteSegment(string $data):QRCode{
		$this->addSegment(new Byte($data));

		return $this;
	}

	/**
	 * ISO/IEC 18004:2000 8.3.1 - Extended Channel Interpretation (ECI) Mode
	 */
	public function addEciDesignator(int $encoding):QRCode{
		$this->addSegment(new ECI($encoding));

		return $this;
	}

	/**
	 * i hate this somehow but i'll leave it for now
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function addEciSegment(int $encoding, string $data):QRCode{
		// validate the encoding id
		$eciCharset = new ECICharset($encoding);
		// get charset name
		$eciCharsetName = $eciCharset->getName();
		// convert the string to the given charset
		if($eciCharsetName !== null){
			$data = mb_convert_encoding($data, $eciCharsetName, mb_detect_encoding($data));
			// add ECI designator
			$this->addSegment(new ECI($eciCharset->getID()));
			$this->addSegment(new Byte($data));

			return $this;
		}

		throw new QRCodeException('unable to add ECI segment');
	}

}
