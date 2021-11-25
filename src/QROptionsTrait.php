<?php
/**
 * Trait QROptionsTrait
 *
 * @filesource   QROptionsTrait.php
 * @created      10.03.2018
 * @package      chillerlan\QRCode
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */

namespace chillerlan\QRCode;

use function array_values, count, in_array, is_numeric, max, min, sprintf, strtolower;

/**
 * The QRCode plug-in settings & setter functionality
 */
trait QROptionsTrait{

	/**
	 * QR Code version number
	 *
	 * [1 ... 40] or QRCode::VERSION_AUTO
	 */
	protected int $version = QRCode::VERSION_AUTO;

	/**
	 * Minimum QR version
	 *
	 * if $version = QRCode::VERSION_AUTO
	 */
	protected int $versionMin = 1;

	/**
	 * Maximum QR version
	 */
	protected int $versionMax = 40;

	/**
	 * Error correct level
	 *
	 * QRCode::ECC_X where X is:
	 *
	 *   - L =>  7%
	 *   - M => 15%
	 *   - Q => 25%
	 *   - H => 30%
	 */
	protected int $eccLevel = QRCode::ECC_L;

	/**
	 * Mask Pattern to use
	 *
	 * [0...7] or QRCode::MASK_PATTERN_AUTO
	 */
	protected int $maskPattern = QRCode::MASK_PATTERN_AUTO;

	/**
	 * Add a "quiet zone" (margin) according to the QR code spec
	 */
	protected bool $addQuietzone = true;

	/**
	 * Size of the quiet zone
	 *
	 * internally clamped to [0 ... $moduleCount / 2], defaults to 4 modules
	 */
	protected int $quietzoneSize = 4;

	/**
	 * Use this to circumvent the data mode detection and force the usage of the given mode.
	 *
	 * valid modes are: Number, AlphaNum, Kanji, Byte (case insensitive)
	 *
	 * @see https://github.com/chillerlan/php-qrcode/issues/39
     * @see https://github.com/chillerlan/php-qrcode/issues/97 (changed default value to '')
	 */
	protected string $dataModeOverride = '';

	/**
	 * The output type
	 *
	 *   - QRCode::OUTPUT_MARKUP_XXXX where XXXX = HTML, SVG
	 *   - QRCode::OUTPUT_IMAGE_XXX where XXX = PNG, GIF, JPG
	 *   - QRCode::OUTPUT_STRING_XXXX where XXXX = TEXT, JSON
	 *   - QRCode::OUTPUT_CUSTOM
	 */
	protected string $outputType = QRCode::OUTPUT_IMAGE_PNG;

	/**
	 * the FQCN of the custom QROutputInterface if $outputType is set to QRCode::OUTPUT_CUSTOM
	 */
	protected ?string $outputInterface = null;

	/**
	 * /path/to/cache.file
	 */
	protected ?string $cachefile = null;

	/**
	 * newline string [HTML, SVG, TEXT]
	 */
	protected string $eol = PHP_EOL;

	/**
	 * size of a QR code pixel [SVG, IMAGE_*], HTML via CSS
	 */
	protected int $scale = 5;

	/**
	 * a common css class
	 */
	protected string $cssClass = '';

	/**
	 * SVG opacity
	 */
	protected float $svgOpacity = 1.0;

	/**
	 * anything between <defs>
	 *
	 * @see https://developer.mozilla.org/docs/Web/SVG/Element/defs
	 */
	protected string $svgDefs = '<style>rect{shape-rendering:crispEdges}</style>';

	/**
	 * SVG viewBox size. a single integer number which defines width/height of the viewBox attribute.
	 *
	 * viewBox="0 0 x x"
	 *
	 * @see https://css-tricks.com/scale-svg/#article-header-id-3
	 */
	protected ?int $svgViewBoxSize = null;

	/**
	 * string substitute for dark
	 */
	protected string $textDark = '🔴';

	/**
	 * string substitute for light
	 */
	protected string $textLight = '⭕';

	/**
	 * markup substitute for dark (CSS value)
	 */
	protected string $markupDark = '#000';

	/**
	 * markup substitute for light (CSS value)
	 */
	protected string $markupLight = '#fff';

	/**
	 * Return the image resource instead of a render if applicable.
	 * This option overrides other output options, such as $cachefile and $imageBase64.
	 *
	 * Supported by the following modules:
	 *
	 * - QRImage:   resource (PHP < 8), GdImage
	 * - QRImagick: Imagick
	 * - QRFpdf:    FPDF
	 *
	 * @see \chillerlan\QRCode\Output\QROutputInterface::dump()
	 *
	 * @var bool
	 */
	protected bool $returnResource = false;

	/**
	 * toggle base64 or raw image data
	 */
	protected bool $imageBase64 = true;

	/**
	 * toggle transparency, not supported by jpg
	 */
	protected bool $imageTransparent = true;

	/**
	 * @see imagecolortransparent()
	 *
	 * [R, G, B]
	 */
	protected array $imageTransparencyBG = [255, 255, 255];

	/**
	 * @see imagepng()
	 */
	protected int $pngCompression = -1;

	/**
	 * @see imagejpeg()
	 */
	protected int $jpegQuality = 85;

	/**
	 * Imagick output format
	 *
	 * @see \Imagick::setType()
	 */
	protected string $imagickFormat = 'png';

	/**
	 * Imagick background color (defaults to "transparent")
	 *
	 * @see \ImagickPixel::__construct()
	 */
	protected ?string $imagickBG = null;

	/**
	 * Measurement unit for FPDF output: pt, mm, cm, in (defaults to "pt")
	 *
	 * @see \FPDF::__construct()
	 */
	protected string $fpdfMeasureUnit = 'pt';

	/**
	 * Module values map
	 *
	 *   - HTML, IMAGICK: #ABCDEF, cssname, rgb(), rgba()...
	 *   - IMAGE: [63, 127, 255] // R, G, B
	 */
	protected ?array $moduleValues = null;

	/**
	 * clamp min/max version number
	 */
	protected function setMinMaxVersion(int $versionMin, int $versionMax):void{
		$min = max(1, min(40, $versionMin));
		$max = max(1, min(40, $versionMax));

		$this->versionMin = min($min, $max);
		$this->versionMax = max($min, $max);
	}

	/**
	 * sets the minimum version number
	 */
	protected function set_versionMin(int $version):void{
		$this->setMinMaxVersion($version, $this->versionMax);
	}

	/**
	 * sets the maximum version number
	 */
	protected function set_versionMax(int $version):void{
		$this->setMinMaxVersion($this->versionMin, $version);
	}

	/**
	 * sets the error correction level
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function set_eccLevel(int $eccLevel):void{

		if(!isset(QRCode::ECC_MODES[$eccLevel])){
			throw new QRCodeException(sprintf('Invalid error correct level: %s', $eccLevel));
		}

		$this->eccLevel = $eccLevel;
	}

	/**
	 * sets/clamps the mask pattern
	 */
	protected function set_maskPattern(int $maskPattern):void{

		if($maskPattern !== QRCode::MASK_PATTERN_AUTO){
			$this->maskPattern = max(0, min(7, $maskPattern));
		}

	}

	/**
	 * sets the transparency background color
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function set_imageTransparencyBG(array $imageTransparencyBG):void{

		// invalid value - set to white as default
		if(count($imageTransparencyBG) < 3){
			$this->imageTransparencyBG = [255, 255, 255];

			return;
		}

		foreach($imageTransparencyBG as $k => $v){

			// cut off exceeding items
			if($k > 2){
				break;
			}

			if(!is_numeric($v)){
				throw new QRCodeException('Invalid RGB value.');
			}

			// clamp the values
			$this->imageTransparencyBG[$k] = max(0, min(255, (int)$v));
		}

		// use the array values to not run into errors with the spread operator (...$arr)
		$this->imageTransparencyBG = array_values($this->imageTransparencyBG);
	}

	/**
	 * sets/clamps the version number
	 */
	protected function set_version(int $version):void{

		if($version !== QRCode::VERSION_AUTO){
			$this->version = max(1, min(40, $version));
		}

	}

	/**
	 * sets the FPDF measurement unit
	 *
	 * @codeCoverageIgnore
	 */
	protected function set_fpdfMeasureUnit(string $unit):void{
		$unit = strtolower($unit);

		if(in_array($unit, ['cm', 'in', 'mm', 'pt'], true)){
			$this->fpdfMeasureUnit = $unit;
		}

		// @todo throw or ignore silently?
	}

}
