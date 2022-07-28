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

use chillerlan\QRCode\Common\{EccLevel, ECICharset, MaskPattern, Mode, Version};
use chillerlan\QRCode\Data\{AlphaNum, Byte, ECI, Kanji, Number, QRCodeDataException, QRData, QRDataModeInterface, QRMatrix};
use chillerlan\QRCode\Decoder\{Decoder, DecoderResult, GDLuminanceSource, IMagickLuminanceSource, LuminanceSourceInterface};
use chillerlan\QRCode\Output\{QRCodeOutputException, QROutputInterface};
use chillerlan\Settings\SettingsContainerInterface;
use function class_exists, class_implements, in_array, mb_convert_encoding, mb_detect_encoding;

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

	/**
	 * @deprecated 5.0.0 use Version::AUTO instead
	 * @see \chillerlan\QRCode\Common\Version::AUTO
	 * @var int
	 */
	public const VERSION_AUTO      = Version::AUTO;

	/**
	 * @deprecated 5.0.0 use MaskPattern::AUTO instead
	 * @see \chillerlan\QRCode\Common\MaskPattern::AUTO
	 * @var int
	 */
	public const MASK_PATTERN_AUTO = MaskPattern::AUTO;

	/**
	 * @deprecated 5.0.0 use EccLevel::L instead
	 * @see \chillerlan\QRCode\Common\EccLevel::L
	 * @var int
	 */
	public const ECC_L = EccLevel::L;

	/**
	 * @deprecated 5.0.0 use EccLevel::M instead
	 * @see \chillerlan\QRCode\Common\EccLevel::M
	 * @var int
	 */
	public const ECC_M = EccLevel::M;

	/**
	 * @deprecated 5.0.0 use EccLevel::Q instead
	 * @see \chillerlan\QRCode\Common\EccLevel::Q
	 * @var int
	 */
	public const ECC_Q = EccLevel::Q;

	/**
	 * @deprecated 5.0.0 use EccLevel::H instead
	 * @see \chillerlan\QRCode\Common\EccLevel::H
	 * @var int
	 */
	public const ECC_H = EccLevel::H;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::MARKUP_HTML instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::MARKUP_HTML
	 * @var string
	 */
	public const OUTPUT_MARKUP_HTML = QROutputInterface::MARKUP_HTML;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::MARKUP_SVG instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::MARKUP_SVG
	 * @var string
	 */
	public const OUTPUT_MARKUP_SVG  = QROutputInterface::MARKUP_SVG;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::GDIMAGE_PNG instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG
	 * @var string
	 */
	public const OUTPUT_IMAGE_PNG   = QROutputInterface::GDIMAGE_PNG;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::GDIMAGE_JPG instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_JPG
	 * @var string
	 */
	public const OUTPUT_IMAGE_JPG   = QROutputInterface::GDIMAGE_JPG;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::GDIMAGE_GIF instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_GIF
	 * @var string
	 */
	public const OUTPUT_IMAGE_GIF   = QROutputInterface::GDIMAGE_GIF;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::STRING_JSON instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::STRING_JSON
	 * @var string
	 */
	public const OUTPUT_STRING_JSON = QROutputInterface::STRING_JSON;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::STRING_TEXT instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::STRING_TEXT
	 * @var string
	 */
	public const OUTPUT_STRING_TEXT = QROutputInterface::STRING_TEXT;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::IMAGICK instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::IMAGICK
	 * @var string
	 */
	public const OUTPUT_IMAGICK     = QROutputInterface::IMAGICK;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::FPDF instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::FPDF
	 * @var string
	 */
	public const OUTPUT_FPDF        = QROutputInterface::FPDF;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::EPS instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::EPS
	 * @var string
	 */
	public const OUTPUT_EPS         = QROutputInterface::EPS;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::CUSTOM instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::CUSTOM
	 * @var string
	 */
	public const OUTPUT_CUSTOM      = QROutputInterface::CUSTOM;

	/**
	 * @deprecated 5.0.0 use QROutputInterface::MODES instead
	 * @see \chillerlan\QRCode\Output\QROutputInterface::MODES
	 * @var string[]
	 */
	public const OUTPUT_MODES       = QROutputInterface::MODES;

	/**
	 * The settings container
	 *
	 * @var \chillerlan\QRCode\QROptions|\chillerlan\Settings\SettingsContainerInterface
	 */
	protected SettingsContainerInterface $options;

	/**
	 * A collection of one or more data segments of [classname, data] to write
	 *
	 * @see \chillerlan\QRCode\Data\QRDataModeInterface
	 *
	 * @var \chillerlan\QRCode\Data\QRDataModeInterface[]
	 */
	protected array $dataSegments = [];

	/**
	 * The luminance source for the reader
	 */
	protected string $luminanceSourceFQN = GDLuminanceSource::class;

	/**
	 * QRCode constructor.
	 *
	 * Sets the options instance
	 */
	public function __construct(SettingsContainerInterface $options = null){
		$this->options = $options ?? new QROptions;

		// i hate this less
		if($this->options->readerUseImagickIfAvailable){
			$this->luminanceSourceFQN = IMagickLuminanceSource::class;
		}
	}

	/**
	 * Renders a QR Code for the given $data and QROptions, saves $file optionally
	 *
	 * @return mixed
	 */
	public function render(string $data = null, string $file = null){

		if($data !== null){
			/** @var \chillerlan\QRCode\Data\QRDataModeInterface $dataInterface */
			foreach(Mode::INTERFACES as $dataInterface){

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

		$dataInterface = new QRData($this->options, $this->dataSegments);
		$maskPattern   = $this->options->maskPattern === MaskPattern::AUTO
			? MaskPattern::getBestPattern($dataInterface)
			: new MaskPattern($this->options->maskPattern);

		$matrix = $dataInterface->writeMatrix($maskPattern);

		// add matrix modifications after mask pattern evaluation and before handing over to output
		if($this->options->addLogoSpace){
			$matrix->setLogoSpace(
				$this->options->logoSpaceWidth,
				$this->options->logoSpaceHeight,
				$this->options->logoSpaceStartX,
				$this->options->logoSpaceStartY
			);
		}

		if($this->options->addQuietzone){
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

		if($this->options->outputType === QROutputInterface::CUSTOM){
			return $this->initCustomOutputInterface();
		}

		$outputInterface = QROutputInterface::MODES[$this->options->outputType] ?? false;

		if($outputInterface){
			return new $outputInterface($this->options, $this->getMatrix());
		}

		throw new QRCodeOutputException('invalid output type');
	}

	/**
	 * initializes a custom output module after checking the existence of the class and if it implemnts the required interface
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function initCustomOutputInterface():QROutputInterface{

		if(!class_exists($this->options->outputInterface)){
			throw new QRCodeOutputException('invalid custom output module');
		}

		if(!in_array(QROutputInterface::class, class_implements($this->options->outputInterface))){
			throw new QRCodeOutputException('custom output module does not implement QROutputInterface');
		}

		/** @phan-suppress-next-line PhanTypeExpectedObjectOrClassName */
		return new $this->options->outputInterface($this->options, $this->getMatrix());
	}

	/**
	 * checks if a string qualifies as numeric (convenience method)
	 *
	 * @deprecated 5.0.0 use Number::validateString() instead
	 * @see \chillerlan\QRCode\Data\Number::validateString()
	 * @codeCoverageIgnore
	 */
	public function isNumber(string $string):bool{
		return Number::validateString($string);
	}

	/**
	 * checks if a string qualifies as alphanumeric (convenience method)
	 *
	 * @deprecated 5.0.0 use AlphaNum::validateString() instead
	 * @see \chillerlan\QRCode\Data\AlphaNum::validateString()
	 * @codeCoverageIgnore
	 */
	public function isAlphaNum(string $string):bool{
		return AlphaNum::validateString($string);
	}

	/**
	 * checks if a string qualifies as Kanji (convenience method)
	 *
	 * @deprecated 5.0.0 use Kanji::validateString() instead
	 * @see \chillerlan\QRCode\Data\Kanji::validateString()
	 * @codeCoverageIgnore
	 */
	public function isKanji(string $string):bool{
		return Kanji::validateString($string);
	}

	/**
	 * a dummy (convenience method)
	 *
	 * @deprecated 5.0.0 use Byte::validateString() instead
	 * @see \chillerlan\QRCode\Data\Byte::validateString()
	 * @codeCoverageIgnore
	 */
	public function isByte(string $string):bool{
		return Byte::validateString($string);
	}

	/**
	 * Adds a data segment
	 *
	 * ISO/IEC 18004:2000 8.3.6 - Mixing modes
	 * ISO/IEC 18004:2000 Annex H - Optimisation of bit stream length
	 */
	protected function addSegment(QRDataModeInterface $segment):void{
		$this->dataSegments[] = $segment;
	}

	/**
	 * Clears the data segments array
	 */
	public function clearSegments():self{
		$this->dataSegments = [];

		return $this;
	}

	/**
	 * Adds a numeric data segment
	 *
	 * ISO/IEC 18004:2000 8.3.2 - Numeric Mode
	 */
	public function addNumericSegment(string $data):self{
		$this->addSegment(new Number($data));

		return $this;
	}

	/**
	 * Adds an alphanumeric data segment
	 *
	 * ISO/IEC 18004:2000 8.3.3 - Alphanumeric Mode
	 */
	public function addAlphaNumSegment(string $data):self{
		$this->addSegment(new AlphaNum($data));

		return $this;
	}

	/**
	 * Adds a Kanji data segment
	 *
	 * ISO/IEC 18004:2000 8.3.5 - Kanji Mode
	 */
	public function addKanjiSegment(string $data):self{
		$this->addSegment(new Kanji($data));

		return $this;
	}

	/**
	 * Adds an 8-bit byte data segment
	 *
	 * ISO/IEC 18004:2000 8.3.4 - 8-bit Byte Mode
	 */
	public function addByteSegment(string $data):self{
		$this->addSegment(new Byte($data));

		return $this;
	}

	/**
	 * Adds a standalone ECI designator
	 *
	 * ISO/IEC 18004:2000 8.3.1 - Extended Channel Interpretation (ECI) Mode
	 */
	public function addEciDesignator(int $encoding):self{
		$this->addSegment(new ECI($encoding));

		return $this;
	}

	/**
	 * Adds an ECI data segment (including designator)
	 *
	 * i hate this somehow but i'll leave it for now
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function addEciSegment(int $encoding, string $data):self{
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

	/**
	 * Reads a QR Code from a given file
	 *
	 * @noinspection PhpUndefinedMethodInspection
	 */
	public function readFromFile(string $path):DecoderResult{
		return $this->readFromSource($this->luminanceSourceFQN::fromFile($path, $this->options));
	}

	/**
	 * Reads a QR Code from the given data blob
	 *
	 *  @noinspection PhpUndefinedMethodInspection
	 */
	public function readFromBlob(string $blob):DecoderResult{
		return $this->readFromSource($this->luminanceSourceFQN::fromBlob($blob, $this->options));
	}

	/**
	 * Reads a QR Code from the given luminance source
	 */
	public function readFromSource(LuminanceSourceInterface $source):DecoderResult{
		return (new Decoder)->decode($source);
	}

}
