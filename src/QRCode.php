<?php
/**
 * Class QRCode
 *
 * @created      26.11.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode;

use chillerlan\QRCode\Common\{
	ECICharset, GDLuminanceSource, IMagickLuminanceSource, LuminanceSourceInterface, MaskPattern, Mode
};
use chillerlan\QRCode\Data\{AlphaNum, Byte, ECI, Hanzi, Kanji, Number, QRData, QRDataModeInterface, QRMatrix};
use chillerlan\QRCode\Decoder\{Decoder, DecoderResult};
use chillerlan\QRCode\Output\{QRCodeOutputException, QROutputInterface};
use chillerlan\Settings\SettingsContainerInterface;
use function class_exists, class_implements, in_array, is_iterable, mb_convert_encoding, mb_internal_encoding;

/**
 * Turns a text string into a Model 2 QR Code
 *
 * @see https://github.com/kazuhikoarase/qrcode-generator/tree/master/php
 * @see https://www.qrcode.com/en/codes/model12.html
 * @see https://www.swisseduc.ch/informatik/theoretische_informatik/qr_codes/docs/qr_standard.pdf
 * @see https://en.wikipedia.org/wiki/QR_code
 * @see https://www.thonky.com/qr-code-tutorial/
 */
class QRCode{

	/**
	 * The settings container
	 */
	protected SettingsContainerInterface|QROptions $options;

	/**
	 * A collection of one or more data segments of QRDataModeInterface instances to write
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
	 * @phpstan-param array<string, mixed> $options
	 */
	public function __construct(SettingsContainerInterface|QROptions|iterable $options = new QROptions){
		$this->setOptions($options);
	}

	/**
	 * Sets an options instance
	 *
	 * @phpstan-param array<string, mixed> $options
	 */
	public function setOptions(SettingsContainerInterface|QROptions|iterable $options):static{

		if(is_iterable($options)){
			$options = new QROptions($options);
		}

		$this->options = $options;

		if($this->options->readerUseImagickIfAvailable){
			$this->luminanceSourceFQN = IMagickLuminanceSource::class;
		}

		return $this;
	}

	/**
	 * Renders a QR Code for the given $data and QROptions, saves $file optionally
	 */
	public function render(string|null $data = null, string|null $file = null):mixed{

		if($data !== null){
			foreach(Mode::INTERFACES as $dataInterface){

				if($dataInterface::validateString($data)){
					$this->addSegment(new $dataInterface($data));

					break;
				}
			}
		}

		return $this->renderMatrix($this->getQRMatrix(), $file);
	}

	/**
	 * Renders a QR Code for the given QRMatrix and QROptions, saves $file optionally
	 */
	public function renderMatrix(QRMatrix $matrix, string|null $file = null):mixed{
		return $this->initOutputInterface($matrix)->dump($file ?? $this->options->cachefile);
	}

	/**
	 * Returns a QRMatrix object for the given $data and current QROptions
	 */
	public function getQRMatrix():QRMatrix{
		$matrix = (new QRData($this->options, $this->dataSegments))->writeMatrix();

		$maskPattern = $this->options->maskPattern === MaskPattern::AUTO
			? MaskPattern::getBestPattern($matrix)
			: new MaskPattern($this->options->maskPattern);

		$matrix->setFormatInfo($maskPattern)->mask($maskPattern);

		return $this->addMatrixModifications($matrix);
	}

	/**
	 * add matrix modifications after mask pattern evaluation and before handing over to output
	 */
	protected function addMatrixModifications(QRMatrix $matrix):QRMatrix{

		if($this->options->addLogoSpace){
			// check whether one of the dimensions was omitted
			$logoSpaceWidth  = ($this->options->logoSpaceWidth ?? $this->options->logoSpaceHeight ?? 0);
			$logoSpaceHeight = ($this->options->logoSpaceHeight ?? $logoSpaceWidth);

			$matrix->setLogoSpace(
				$logoSpaceWidth,
				$logoSpaceHeight,
				$this->options->logoSpaceStartX,
				$this->options->logoSpaceStartY,
			);
		}

		if($this->options->addQuietzone){
			$matrix->setQuietZone($this->options->quietzoneSize);
		}

		return $matrix;
	}

	/**
	 * initializes a fresh built-in or custom QROutputInterface
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function initOutputInterface(QRMatrix $matrix):QROutputInterface{
		$outputInterface = $this->options->outputInterface;

		if(empty($outputInterface) || !class_exists($outputInterface)){
			throw new QRCodeOutputException('invalid output class');
		}

		if(!in_array(QROutputInterface::class, class_implements($outputInterface), true)){
			throw new QRCodeOutputException('output class does not implement QROutputInterface');
		}

		/** @var \chillerlan\QRCode\Output\QROutputInterface $instance */
		$instance = new $outputInterface($this->options, $matrix);

		return $instance;
	}

	/**
	 * Adds a data segment
	 *
	 * ISO/IEC 18004:2000 8.3.6 - Mixing modes
	 * ISO/IEC 18004:2000 Annex H - Optimisation of bit stream length
	 */
	public function addSegment(QRDataModeInterface $segment):static{
		$this->dataSegments[] = $segment;

		return $this;
	}

	/**
	 * Clears the data segments array
	 *
	 * @codeCoverageIgnore
	 */
	public function clearSegments():static{
		$this->dataSegments = [];

		return $this;
	}

	/**
	 * Adds a numeric data segment
	 *
	 * ISO/IEC 18004:2000 8.3.2 - Numeric Mode
	 */
	public function addNumericSegment(string $data):static{
		return $this->addSegment(new Number($data));
	}

	/**
	 * Adds an alphanumeric data segment
	 *
	 * ISO/IEC 18004:2000 8.3.3 - Alphanumeric Mode
	 */
	public function addAlphaNumSegment(string $data):static{
		return $this->addSegment(new AlphaNum($data));
	}

	/**
	 * Adds a Kanji data segment (Japanese 13-bit double-byte characters, Shift-JIS)
	 *
	 * ISO/IEC 18004:2000 8.3.5 - Kanji Mode
	 */
	public function addKanjiSegment(string $data):static{
		return $this->addSegment(new Kanji($data));
	}

	/**
	 * Adds a Hanzi data segment (simplified Chinese 13-bit double-byte characters, GB2312/GB18030)
	 *
	 * GBT18284-2000 Hanzi Mode
	 */
	public function addHanziSegment(string $data):static{
		return $this->addSegment(new Hanzi($data));
	}

	/**
	 * Adds an 8-bit byte data segment
	 *
	 * ISO/IEC 18004:2000 8.3.4 - 8-bit Byte Mode
	 */
	public function addByteSegment(string $data):static{
		return $this->addSegment(new Byte($data));
	}

	/**
	 * Adds a standalone ECI designator
	 *
	 * The ECI designator must be followed by a Byte segment that contains the string encoded according to the given ECI charset
	 *
	 * ISO/IEC 18004:2000 8.3.1 - Extended Channel Interpretation (ECI) Mode
	 */
	public function addEciDesignator(int $encoding):static{
		return $this->addSegment(new ECI($encoding));
	}

	/**
	 * Adds an ECI data segment (including designator)
	 *
	 * The given string will be encoded from mb_internal_encoding() to the given ECI character set
	 *
	 * I hate this somehow, but I'll leave it for now
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function addEciSegment(int $encoding, string $data):static{
		// validate the encoding id
		$eciCharset = new ECICharset($encoding);
		// get charset name
		$eciCharsetName = $eciCharset->getName();
		// convert the string to the given charset
		if($eciCharsetName !== null){
			$data = mb_convert_encoding($data, $eciCharsetName, mb_internal_encoding());

			return $this
				->addEciDesignator($eciCharset->getID())
				->addByteSegment($data)
			;
		}

		throw new QRCodeException('unable to add ECI segment');
	}

	/**
	 * Reads a QR Code from a given file
	 */
	public function readFromFile(string $path):DecoderResult{
		return $this->readFromSource($this->luminanceSourceFQN::fromFile($path, $this->options));
	}

	/**
	 * Reads a QR Code from the given data blob
	 */
	public function readFromBlob(string $blob):DecoderResult{
		return $this->readFromSource($this->luminanceSourceFQN::fromBlob($blob, $this->options));
	}

	/**
	 * Reads a QR Code from the given luminance source
	 */
	public function readFromSource(LuminanceSourceInterface $source):DecoderResult{
		return (new Decoder($this->options))->decode($source);
	}

}
