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
use function array_values, count, extension_loaded, in_array, is_numeric, max, min, sprintf, strtolower;

/**
 * The QRCode plug-in settings & setter functionality
 */
trait QROptionsTrait{

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
	protected string $cssClass = 'qrcode';

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

	/**
	 * whether to connect the paths for the several module types to avoid weird glitches when using gradients etc.
	 *
	 * @see https://github.com/chillerlan/php-qrcode/issues/57
	 */
	protected bool $connectPaths = false;

	/**
	 * specify which paths/patterns to exclude from connecting if $svgConnectPaths is set to true
	 */
	protected array $excludeFromConnect = [];

	/**
	 * specify whether to draw the modules as filled circles
	 *
	 * a note for GDImage output:
	 *
	 * if QROptions::$scale is less or equal than 20, the image will be upscaled internally, then the modules will be drawn
	 * using imagefilledellipse() and then scaled back to the expected size using IMG_BICUBIC which in turn produces
	 * unexpected outcomes in combination with transparency - to avoid this, set scale to a value greater than 20.
	 *
	 * @see https://github.com/chillerlan/php-qrcode/issues/23
	 * @see https://github.com/chillerlan/php-qrcode/discussions/122
	 */
	protected bool $drawCircularModules = false;

	/**
	 * specifies the radius of the modules when $svgDrawCircularModules is set to true
	 */
	protected float $circleRadius = 0.45;

	/**
	 * specifies which module types to exclude when $svgDrawCircularModules is set to true
	 */
	protected array $keepAsSquare = [];

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
	 * toggle background transparency
	 *
	 * - GdImage: (png, gif) it sets imagecolortransparent() with {@see \chillerlan\QRCode\QROptions::$imageTransparencyBG}
	 *
	 *
	 * @see https://github.com/chillerlan/php-qrcode/discussions/121
 	 */
	protected bool $imageTransparent = true;

	/**
	 * whether to draw the light (false) modules
	 *
	 * @var bool
	 */
	protected bool $drawLightModules = true;

	/**
	 * Sets the background color in GD mode: [R, G, B].
	 *
	 * When $imageTransparent is set to true, this color is set as transparent in imagecolortransparent()
	 *
	 * @see \chillerlan\QRCode\Output\QRGdImage
	 * @see \chillerlan\QRCode\QROptions::$imageTransparent
	 * @see imagecolortransparent()
	 */
	protected array $imageTransparencyBG = [255, 255, 255];

	/**
	 * Sets the image background color (if applicable)
	 *
	 * - Imagick: defaults to "transparent" or "white", depending on $imageTransparent, {@see \ImagickPixel::__construct()}
	 * - GdImage: defaults to $imageTransparencyBG, {@see \chillerlan\QRCode\QROptions::$imageTransparencyBG}
	 *
	 * @var mixed|null
	 */
	protected $bgColor = null;

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
	 * @see \Imagick::setImageFormat()
	 * @see https://www.imagemagick.org/script/formats.php
	 */
	protected string $imagickFormat = 'png32';

	/**
	 * Imagick background color
	 *
	 * @deprecated 5.0.0 use QROptions::$bgColor instead
	 * @see \chillerlan\QRCode\QROptions::$bgColor
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
	 * Toggles logo space creation
	 */
	protected bool $addLogoSpace = false;

	/**
	 * width of the logo space
	 */
	protected int $logoSpaceWidth = 0;

	/**
	 * height of the logo space
	 */
	protected int $logoSpaceHeight = 0;

	/**
	 * optional horizontal start position of the logo space (top left corner)
	 */
	protected ?int $logoSpaceStartX = null;

	/**
	 * optional vertical start position of the logo space (top left corner)
	 */
	protected ?int $logoSpaceStartY = null;

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
		$this->version = $version !== Version::AUTO ? max(1, min(40, $version)) : Version::AUTO;
	}

	/**
	 * sets the error correction level
	 *
	 * @todo: accept string values (PHP8+)
	 * @see https://github.com/chillerlan/php-qrcode/discussions/160
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function set_eccLevel(int $eccLevel):void{

		if((0b11 & $eccLevel) !== $eccLevel){
			throw new QRCodeException(sprintf('Invalid error correct level: %s', $eccLevel));
		}

		$this->eccLevel = $eccLevel;
	}

	/**
	 * sets/clamps the mask pattern
	 */
	protected function set_maskPattern(int $maskPattern):void{

		if($maskPattern !== MaskPattern::AUTO){
			$this->maskPattern = max(0, min(7, $maskPattern));
		}

	}

	/**
	 * sets/clamps the quiet zone size
	 */
	protected function set_quietzoneSize(int $quietzoneSize):void{
		$this->quietzoneSize = max(0, min($quietzoneSize, 75));
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
		$this->readerUseImagickIfAvailable = $useImagickIfAvailable && extension_loaded('imagick');
	}

	/**
	 * clamp the logo space values between 0 and maximum length (177 modules at version 40)
	 */
	protected function clampLogoSpaceValue(int $value):int{
		return (int)max(0, min(177, $value));
	}

	/**
	 * clamp/set logo space width
	 */
	protected function set_logoSpaceWidth(int $value):void{
		$this->logoSpaceWidth = $this->clampLogoSpaceValue($value);
	}

	/**
	 * clamp/set logo space height
	 */
	protected function set_logoSpaceHeight(int $value):void{
		$this->logoSpaceHeight = $this->clampLogoSpaceValue($value);
	}

	/**
	 * clamp/set horizontal logo space start
	 */
	protected function set_logoSpaceStartX(?int $value):void{
		$this->logoSpaceStartX = $value === null ? null : $this->clampLogoSpaceValue($value);
	}

	/**
	 * clamp/set vertical logo space start
	 */
	protected function set_logoSpaceStartY(?int $value):void{
		$this->logoSpaceStartY = $value === null ? null : $this->clampLogoSpaceValue($value);
	}

	/**
	 * clamp/set SVG circle radius
	 */
	protected function set_circleRadius(float $circleRadius):void{
		$this->circleRadius = max(0.1, min(0.75, $circleRadius));
	}

}
