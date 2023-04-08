<?php
/**
 * Trait QROptionsTrait
 *
 * @created      10.03.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */

namespace chillerlan\QRCode;

use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\Common\{EccLevel, MaskPattern, Version};
use function extension_loaded, in_array, max, min, strtolower;

/**
 * The QRCode plug-in settings & setter functionality
 */
trait QROptionsTrait{

	/*
	 * QR Code specific settings
	 */

	/**
	 * QR Code version number
	 *
	 * [1 ... 40] or Version::AUTO
	 */
	protected int $version = Version::AUTO;

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
	 *
	 * @todo: accept string values (PHP8+)
	 * @see https://github.com/chillerlan/php-qrcode/discussions/160
	 */
	protected int $eccLevel = EccLevel::L;

	/**
	 * Mask Pattern to use (no value in using, mostly for unit testing purposes)
	 *
	 * [0...7] or MaskPattern::PATTERN_AUTO
	 */
	protected int $maskPattern = MaskPattern::AUTO;

	/**
	 * Add a "quiet zone" (margin) according to the QR code spec
	 *
	 * @see https://www.qrcode.com/en/howto/code.html
	 */
	protected bool $addQuietzone = true;

	/**
	 * Size of the quiet zone
	 *
	 * internally clamped to [0 ... $moduleCount / 2], defaults to 4 modules
	 */
	protected int $quietzoneSize = 4;


	/*
	 * General output settings
	 */

	/**
	 * The output type
	 *
	 *   - QROutputInterface::MARKUP_XXXX where XXXX = HTML, SVG
	 *   - QROutputInterface::GDIMAGE_XXX where XXX = PNG, GIF, JPG
	 *   - QROutputInterface::STRING_XXXX where XXXX = TEXT, JSON
	 *   - QROutputInterface::IMAGICK
	 *   - QROutputInterface::EPS
	 *   - QROutputInterface::FPDF
	 *   - QROutputInterface::CUSTOM
	 */
	protected string $outputType = QROutputInterface::MARKUP_SVG;

	/**
	 * the FQCN of the custom QROutputInterface if $outputType is set to QRCode::OUTPUT_CUSTOM
	 */
	protected ?string $outputInterface = null;

	/**
	 * Return the image resource instead of a render if applicable.
	 * This option overrides other output options, such as $cachefile and $imageBase64.
	 *
	 * Supported by the following modules:
	 *
	 * - QRGdImage: resource (PHP < 8), GdImage
	 * - QRImagick: Imagick
	 * - QRFpdf:    FPDF
	 *
	 * @see \chillerlan\QRCode\Output\QROutputInterface::dump()
	 *
	 * @var bool
	 */
	protected bool $returnResource = false;

	/**
	 * /path/to/cache.file
	 *
	 * please note that the $file parameter in QRCode::render*() takes precedence over the $cachefile value
	 */
	protected ?string $cachefile = null;

	/**
	 * toggle base64 or raw image data (if applicable)
	 */
	protected bool $imageBase64 = true;

	/**
	 * newline string
	 */
	protected string $eol = PHP_EOL;

	/*
	 * Common visual modifications
	 */

	/**
	 * Sets the image background color (if applicable)
	 *
	 * - QRGdImage: defaults to "white"
	 * - QRImagick: defaults to [255, 255, 255]
	 * - QRFpdf: defaults to blank internally (white page)
	 *
	 * @var mixed|null
	 */
	protected $bgColor = null;

	/**
	 * whether to draw the light (false) modules
	 *
	 * @var bool
	 */
	protected bool $drawLightModules = true;

	/**
	 * specify whether to draw the modules as filled circles
	 *
	 * a note for GDImage output:
	 *
	 * if QROptions::$scale is less than 20, the image will be upscaled internally, then the modules will be drawn
	 * using imagefilledellipse() and then scaled back to the expected size
	 *
	 * No effect in: QREps, QRFpdf, QRMarkupHTML
	 *
	 * @see https://github.com/chillerlan/php-qrcode/issues/23
	 * @see https://github.com/chillerlan/php-qrcode/discussions/122
	 */
	protected bool $drawCircularModules = false;

	/**
	 * specifies the radius of the modules when $drawCircularModules is set to true
	 */
	protected float $circleRadius = 0.45;

	/**
	 * specifies which module types to exclude when $drawCircularModules is set to true
	 */
	protected array $keepAsSquare = [];

	/**
	 * whether to connect the paths for the several module types to avoid weird glitches when using gradients etc.
	 *
	 * @see https://github.com/chillerlan/php-qrcode/issues/57
	 */
	protected bool $connectPaths = false;

	/**
	 * specify which paths/patterns to exclude from connecting if $connectPaths is set to true
	 */
	protected array $excludeFromConnect = [];

	/**
	 * Module values map
	 *
	 *   - QRImagick, QRMarkupHTML, QRMarkupSVG: #ABCDEF, cssname, rgb(), rgba()...
	 *   - QREps, QRFpdf, QRGdImage: [63, 127, 255] // R, G, B
	 */
	protected ?array $moduleValues = null;

	/**
	 * Toggles logo space creation
	 */
	protected bool $addLogoSpace = false;

	/**
	 * width of the logo space
	 *
	 * if only either $logoSpaceWidth or $logoSpaceHeight is given, the logo space is assumed a square of that size
	 */
	protected ?int $logoSpaceWidth = null;

	/**
	 * height of the logo space
	 *
	 * if only either $logoSpaceWidth or $logoSpaceHeight is given, the logo space is assumed a square of that size
	 */
	protected ?int $logoSpaceHeight = null;

	/**
	 * optional horizontal start position of the logo space (top left corner)
	 */
	protected ?int $logoSpaceStartX = null;

	/**
	 * optional vertical start position of the logo space (top left corner)
	 */
	protected ?int $logoSpaceStartY = null;


	/*
	 * Common raster image settings (QRGdImage, QRImagick)
	 */

	/**
	 * pixel size of a QR code module
	 */
	protected int $scale = 5;

	/**
	 * toggle transparency
	 *
	 * - QRGdImage and QRImagick: the given {@see \chillerlan\QRCode\QROptions::$transparencyColor $transparencyColor} is set as transparent
	 *
	 * @see https://github.com/chillerlan/php-qrcode/discussions/121
	 */
	protected bool $imageTransparent = true;

	/**
	 * Sets a transparency color for when {@see \chillerlan\QRCode\QROptions::$imageTransparent QROptions::$imageTransparent} is set to true.
	 * Defaults to {@see \chillerlan\QRCode\QROptions::$bgColor QROptions::$bgColor}.
	 *
	 * - QRGdImage: [R, G, B], this color is set as transparent in {@see imagecolortransparent()}
	 * - QRImagick: "color_str", this color is set in {@see Imagick::transparentPaintImage()}
	 *
	 * @var mixed|null
	 */
	protected $transparencyColor = null;


	/*
	 * QRGdImage settings
	 */

	/**
	 * @see imagepng()
	 */
	protected int $pngCompression = -1;

	/**
	 * @see imagejpeg()
	 */
	protected int $jpegQuality = 85;


	/*
	 * QRImagick settings
	 */

	/**
	 * Imagick output format
	 *
	 * @see \Imagick::setImageFormat()
	 * @see https://www.imagemagick.org/script/formats.php
	 */
	protected string $imagickFormat = 'png32';


	/*
	 * Common markup output settings (QRMarkupSVG, QRMarkupHTML)
	 */

	/**
	 * a common css class
	 */
	protected string $cssClass = 'qrcode';

	/**
	 * markup substitute for dark (CSS value)
	 */
	protected string $markupDark = '#000';

	/**
	 * markup substitute for light (CSS value)
	 */
	protected string $markupLight = '#fff';


	/*
	 * QRMarkupSVG settings
	 */

	/**
	 * SVG opacity
	 */
	protected float $svgOpacity = 1.0;

	/**
	 * anything between <defs>
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Element/defs
	 */
	protected string $svgDefs = '';

	/**
	 * SVG viewBox size. a single integer number which defines width/height of the viewBox attribute.
	 *
	 * viewBox="0 0 x x"
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/viewBox
	 * @see https://css-tricks.com/scale-svg/#article-header-id-3
	 */
	protected ?int $svgViewBoxSize = null;

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/preserveAspectRatio
	 */
	protected string $svgPreserveAspectRatio = 'xMidYMid';

	/**
	 * optional "width" attribute with the specified value (note that the value is not checked!)
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/width
	 */
	protected ?string $svgWidth = null;

	/**
	 * optional "height" attribute with the specified value (note that the value is not checked!)
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/height
	 */
	protected ?string $svgHeight = null;


	/*
	 * QRString settings
	 */

	/**
	 * string substitute for dark
	 */
	protected string $textDark = '🔴';

	/**
	 * string substitute for light
	 */
	protected string $textLight = '⭕';


	/*
	 * QRFpdf settings
	 */

	/**
	 * Measurement unit for FPDF output: pt, mm, cm, in (defaults to "pt")
	 *
	 * @see \FPDF::__construct()
	 */
	protected string $fpdfMeasureUnit = 'pt';


	/*
	 * QR Code reader settings
	 */

	/**
	 * use Imagick (if available) when reading QR Codes
	 */
	protected bool $readerUseImagickIfAvailable = false;

	/**
	 * grayscale the image before reading
	 */
	protected bool $readerGrayscale = false;

	/**
	 * increase the contrast before reading
	 *
	 * note that applying contrast works different in GD and Imagick, so mileage may vary
	 */
	protected bool $readerIncreaseContrast = false;


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
	 * sets/clamps the version number
	 */
	protected function set_version(int $version):void{
		$this->version = ($version !== Version::AUTO) ? max(1, min(40, $version)) : Version::AUTO;
	}

	/**
	 * sets/clamps the quiet zone size
	 */
	protected function set_quietzoneSize(int $quietzoneSize):void{
		$this->quietzoneSize = max(0, min($quietzoneSize, 75));
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

	/**
	 * enables Imagick for the QR Code reader if the extension is available
	 */
	protected function set_readerUseImagickIfAvailable(bool $useImagickIfAvailable):void{
		$this->readerUseImagickIfAvailable = ($useImagickIfAvailable && extension_loaded('imagick'));
	}

	/**
	 * clamp the logo space values between 0 and maximum length (177 modules at version 40)
	 */
	protected function clampLogoSpaceValue(?int $value):?int{

		if($value === null){
			return null;
		}

		return (int)max(0, min(177, $value));
	}

	/**
	 * clamp/set logo space width
	 */
	protected function set_logoSpaceWidth(?int $value):void{
		$this->logoSpaceWidth = $this->clampLogoSpaceValue($value);
	}

	/**
	 * clamp/set logo space height
	 */
	protected function set_logoSpaceHeight(?int $value):void{
		$this->logoSpaceHeight = $this->clampLogoSpaceValue($value);
	}

	/**
	 * clamp/set horizontal logo space start
	 */
	protected function set_logoSpaceStartX(?int $value):void{
		$this->logoSpaceStartX = $this->clampLogoSpaceValue($value);
	}

	/**
	 * clamp/set vertical logo space start
	 */
	protected function set_logoSpaceStartY(?int $value):void{
		$this->logoSpaceStartY = $this->clampLogoSpaceValue($value);
	}

	/**
	 * clamp/set SVG circle radius
	 */
	protected function set_circleRadius(float $circleRadius):void{
		$this->circleRadius = max(0.1, min(0.75, $circleRadius));
	}

}
