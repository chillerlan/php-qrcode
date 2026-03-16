<?php
/**
 * Trait QROptionsTrait
 *
 * Note: the docblocks in this file are optimized for readability in PhpStorm ond on readthedocs.io
 *
 * @created      10.03.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused, PhpComposerExtensionStubsInspection
 * @phan-file-suppress PhanUnreferencedUseFunction, PhanPropertyHookWithDefaultValue
 */
declare(strict_types=1);

namespace chillerlan\QRCode;

use chillerlan\QRCode\Common\{EccLevel, MaskPattern, Version};
use chillerlan\QRCode\Output\QRMarkupSVG;
use function in_array, is_string, max, min, sprintf, strtolower, strtoupper, trim;
use const JSON_THROW_ON_ERROR, JSON_UNESCAPED_SLASHES, PHP_EOL;

/**
 * The QRCode plug-in settings & setter functionality
 *
 * @property int $versionMin
 * @property int $versionMax
 */
trait QROptionsTrait{

	/*
	 * QR Code specific settings
	 */

	/**
	 * QR Code version number
	 *
	 * `1 ... 40` or `Version::AUTO` (default)
	 *
	 * @see \chillerlan\QRCode\Common\Version
	 */
	public int $version = Version::AUTO {
		set{
			$this->version = ($value !== Version::AUTO) ? max(1, min(40, $value)) : Version::AUTO;
		}
	}

	/**
	 * Minimum QR version
	 *
	 * if `QROptions::$version` is set to `Version::AUTO` (default: 1)
	 */
	protected int $versionMin = 1;

	/**
	 * Maximum QR version
	 *
	 * if `QROptions::$version` is set to `Version::AUTO` (default: 40)
	 */
	protected int $versionMax = 40;

	/**
	 * Error correct level
	 *
	 * the constant `EccLevel::X` where `X` is:
	 *
	 * - `L` =>  7% (default)
	 * - `M` => 15%
	 * - `Q` => 25%
	 * - `H` => 30%
	 *
	 * alternatively you can just pass the letters L/M/Q/H (case-insensitive) to the magic setter
	 *
	 * @see \chillerlan\QRCode\Common\EccLevel
	 * @see https://github.com/chillerlan/php-qrcode/discussions/160
	 */
	public int $eccLevel = EccLevel::L {

		/**
		 * sets the ECC level
		 *
		 * @throws \chillerlan\QRCode\QRCodeException
		 */
		set(int|string $eccLevel){

			if(is_string($eccLevel)){
				$ecc = strtoupper(trim($eccLevel));

				if(!in_array($ecc, ['L', 'M', 'Q', 'H'], true)){
					throw new QRCodeException(sprintf('Invalid ECC level: "%s"', $ecc));
				}

				$eccLevel = EccLevel::{$ecc};
			}

			/** @var int $eccLevel */
			if((0b11 & $eccLevel) !== $eccLevel){
				throw new QRCodeException(sprintf('Invalid ECC level: "%s"', $eccLevel));
			}

			$this->eccLevel = $eccLevel;
		}
	}

	/**
	 * Mask Pattern to use (no value in using, mostly for unit testing purposes)
	 *
	 * `0 ... 7` or `MaskPattern::PATTERN_AUTO` (default)
	 *
	 * @see \chillerlan\QRCode\Common\MaskPattern
	 */
	public int $maskPattern = MaskPattern::AUTO;

	/**
	 * Add a "quiet zone" (margin) according to the QR code spec
	 *
	 * @see https://www.qrcode.com/en/howto/code.html
	 */
	public bool $addQuietzone = true;

	/**
	 * Size of the quiet zone
	 *
	 * internally clamped to `0 ... $moduleCount / 2` (default: 4)
	 */
	public int $quietzoneSize = 4 {
		set{
			$this->quietzoneSize = max(0, min($value, 75));
		}
	}

	/*
	 * General output settings
	 */

	/**
	 * The FQCN of the `QROutputInterface` to use
	 */
	public string $outputInterface = QRMarkupSVG::class;

	/**
	 * Return the image resource instead of a render if applicable.
	 *
	 * - `QRGdImage`: `resource` (PHP < 8), `GdImage`
	 * - `QRImagick`: `Imagick`
	 * - `QRFpdf`:    `FPDF`
	 *
	 * This option overrides/ignores other output settings, such as `QROptions::$cachefile`
	 * and `QROptions::$outputBase64`. (default: `false`)
	 *
	 * @see \chillerlan\QRCode\Output\QROutputInterface::dump()
	 */
	public bool $returnResource = false;

	/**
	 * Optional cache file path `/path/to/cache.file`
	 *
	 * Please note that the `$file` parameter in `QRCode::render()` and `QRCode::renderMatrix()`
	 * takes precedence over the `QROptions::$cachefile` value. (default: `null`)
	 *
	 * @see \chillerlan\QRCode\QRCode::render()
	 * @see \chillerlan\QRCode\QRCode::renderMatrix()
	 */
	public string|null $cachefile = null;

	/**
	 * Toggle base64 data URI or raw data output (if applicable)
	 *
	 * (default: `true`)
	 *
	 * @see \chillerlan\QRCode\Output\QROutputAbstract::toBase64DataURI()
	 */
	public bool $outputBase64 = true;

	/**
	 * Newline string
	 *
	 * (default: `PHP_EOL`)
	 */
	public string $eol = PHP_EOL;


	/*
	 * Common visual modifications
	 */

	/**
	 * Sets the image background color (if applicable)
	 *
	 * - `QRImagick`: defaults to `"white"`
	 * - `QRGdImage`: defaults to `[255, 255, 255]`
	 * - `QRFpdf`: defaults to blank internally (white page)
	 */
	public mixed $bgColor = null;

	/**
	 * Whether to invert the matrix (reflectance reversal)
	 *
	 * (default: `false`)
	 *
	 * @see \chillerlan\QRCode\Data\QRMatrix::invert()
	 */
	public bool $invertMatrix = false;

	/**
	 * Whether to draw the light (false) modules
	 *
	 * (default: `true`)
	 */
	public bool $drawLightModules = true;

	/**
	 * Specify whether to draw the modules as filled circles
	 *
	 * a note for `GdImage` output:
	 *
	 * if `QROptions::$scale` is less than 20, the image will be upscaled internally, then the modules will be drawn
	 * using `imagefilledellipse()` and then scaled back to the expected size
	 *
	 * No effect in: `QREps`, `QRFpdf`, `QRMarkupHTML`
	 *
	 * @see \imagefilledellipse()
	 * @see https://github.com/chillerlan/php-qrcode/issues/23
	 * @see https://github.com/chillerlan/php-qrcode/discussions/122
	 */
	public bool $drawCircularModules = false;

	/**
	 * Specifies the radius of the modules when `QROptions::$drawCircularModules` is set to `true`
	 *
	 * (default: 0.45)
	 */
	public float $circleRadius = 0.45 {
		set{
			$this->circleRadius = max(0.1, min(0.75, $value));
		}
	}

	/**
	 * Specifies which module types to exclude when `QROptions::$drawCircularModules` is set to `true`
	 *
	 * (default: `[]`)
	 *
	 * @var int[]
	 */
	public array $keepAsSquare = [];

	/**
	 * Whether to connect the paths for the several module types to avoid weird glitches when using gradients etc.
	 *
	 * This option is exclusive to output classes that use the module collector `QROutputAbstract::collectModules()`,
	 * which converts the `$M_TYPE` of all modules to `QRMatrix::M_DATA` and `QRMatrix::M_DATA_DARK` respectively.
	 *
	 * Module types that should not be added to the connected path can be excluded via `QROptions::$excludeFromConnect`.
	 *
	 * Currentty used in `QREps` and `QRMarkupSVG`.
	 *
	 * @see \chillerlan\QRCode\Output\QROutputAbstract::collectModules()
	 * @see \chillerlan\QRCode\QROptionsTrait::$excludeFromConnect
	 * @see https://github.com/chillerlan/php-qrcode/issues/57
	 */
	public bool $connectPaths = false;

	/**
	 * Specify which paths/patterns to exclude from connecting if `QROptions::$connectPaths` is set to `true`
	 *
	 * @see \chillerlan\QRCode\QROptionsTrait::$connectPaths
	 *
	 * @var int[]
	 */
	public array $excludeFromConnect = [];

	/**
	 * Module values map
	 *
	 * - `QRImagick`, `QRMarkupHTML`, `QRMarkupSVG`: #ABCDEF, cssname, rgb(), rgba()...
	 * - `QREps`, `QRFpdf`, `QRGdImage`: `[R, G, B]` // 0-255
	 * - `QREps`: `[C, M, Y, K]` // 0-255
	 *
	 * @see \chillerlan\QRCode\Output\QROutputAbstract::setModuleValues()
	 *
	 * @var array<int, mixed>
	 */
	public array $moduleValues = [];

	/**
	 * Toggles logo space creation
	 *
	 * @see \chillerlan\QRCode\QRCode::addMatrixModifications()
	 * @see \chillerlan\QRCode\Data\QRMatrix::setLogoSpace()
	 */
	public bool $addLogoSpace = false;

	/**
	 * Width of the logo space
	 *
	 * if only `QROptions::$logoSpaceWidth` is given, the logo space is assumed a square of that size
	 */
	public int|null $logoSpaceWidth = null {
		set{
			$this->logoSpaceWidth = $this->clampLogoSpaceValue($value);
		}
	}

	/**
	 * Height of the logo space
	 *
	 * if only `QROptions::$logoSpaceHeight` is given, the logo space is assumed a square of that size
	 */
	public int|null $logoSpaceHeight = null {
		set{
			$this->logoSpaceHeight = $this->clampLogoSpaceValue($value);
		}
	}

	/**
	 * Optional horizontal start position of the logo space (top left corner)
	 */
	public int|null $logoSpaceStartX = null {
		set{
			$this->logoSpaceStartX = $this->clampLogoSpaceValue($value);
		}
	}

	/**
	 * Optional vertical start position of the logo space (top left corner)
	 */
	public int|null $logoSpaceStartY = null {
		set{
			$this->logoSpaceStartY = $this->clampLogoSpaceValue($value);
		}
	}

	/*
	 * Common raster image settings (QRGdImage, QRImagick)
	 */

	/**
	 * Pixel size of a QR code module
	 */
	public int $scale = 5 {
		set{
			$this->scale = max(1, min($value, 50));
		}
	}

	/**
	 * Toggle transparency
	 *
	 * - `QRGdImage` and `QRImagick`: the given `QROptions::$transparencyColor` is set as transparent
	 *
	 * @see https://github.com/chillerlan/php-qrcode/discussions/121
	 */
	public bool $imageTransparent = false;

	/**
	 * Sets a transparency color for when `QROptions::$imageTransparent` is set to `true`.
	 *
	 * Defaults to `QROptions::$bgColor`.
	 *
	 * - `QRGdImage`: `[R, G, B]`, this color is set as transparent in `imagecolortransparent()`
	 * - `QRImagick`: `"color_str"`, this color is set in `Imagick::transparentPaintImage()`
	 *
	 * @see \imagecolortransparent()
	 * @see \Imagick::transparentPaintImage()
	 */
	public mixed $transparencyColor = null;

	/**
	 * Compression quality
	 *
	 * The given value depends on the used output type:
	 *
	 * - `QRGdImageBMP`:  `[0...1]`
	 * - `QRGdImageJPEG`: `[0...100]`
	 * - `QRGdImageWEBP`: `[0...9]`
	 * - `QRGdImagePNG`:  `[0...100]`
	 * - `QRImagick`:     `[0...100]`
	 *
	 * @see \imagebmp()
	 * @see \imagejpeg()
	 * @see \imagepng()
	 * @see \imagewebp()
	 * @see \Imagick::setImageCompressionQuality()
	 */
	public int $quality = -1;


	/*
	 * QRGdImage settings
	 */

	/**
	 * Toggles the usage of internal upscaling when `QROptions::$drawCircularModules` is set to `true` and
	 * `QROptions::$scale` is less than 20
	 *
	 * @see \chillerlan\QRCode\Output\QRGdImage::createImage()
	 * @see https://github.com/chillerlan/php-qrcode/issues/23
	 */
	public bool $gdImageUseUpscale = true;


	/*
	 * QRImagick settings
	 */

	/**
	 * Imagick output format
	 *
	 * @see \Imagick::setImageFormat()
	 * @see https://www.imagemagick.org/script/formats.php
	 */
	public string $imagickFormat = 'png32';


	/*
	 * Common markup output settings (QRMarkupSVG, QRMarkupHTML)
	 */

	/**
	 * A common css class
	 */
	public string $cssClass = 'qrcode';


	/*
	 * QRMarkupSVG settings
	 */

	/**
	 * Whether to add an XML header line or not, e.g. to embed the SVG directly in HTML
	 *
	 * `<?xml version="1.0" encoding="UTF-8"?>`
	 */
	public bool $svgAddXmlHeader = true;

	/**
	 * Anything in the SVG `<defs>` tag
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Element/defs
	 */
	public string $svgDefs = '';

	/**
	 * Sets the value for the "preserveAspectRatio" on the `<svg>` element
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/preserveAspectRatio
	 */
	public string $svgPreserveAspectRatio = 'xMidYMid';

	/**
	 * Whether to use the SVG `fill` attributes
	 *
	 * If set to `true` (default), the `fill` attribute will be set with the module value for the `<path>` element's `$M_TYPE`.
	 * When set to `false`, the module values map will be ignored and the QR Code may be styled via CSS.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/fill
	 */
	public bool $svgUseFillAttributes = true;


	/*
	 * QRStringText settings
	 */

	/**
	 * An optional line prefix, e.g. empty space to align the QR Code in a console
	 */
	public string $textLineStart = '';


	/*
	 * QRStringJSON settings
	 */

	/**
	 * Sets the flags to use for the `json_encode()` call
	 *
	 * @see https://www.php.net/manual/json.constants.php
	 */
	public int $jsonFlags = (JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);


	/*
	 * QRFpdf settings
	 */

	/**
	 * Measurement unit for `FPDF` output: `pt`, `mm`, `cm`, `in` (default: `pt`)
	 *
	 * @see FPDF::__construct()
	 */
	public string $fpdfMeasureUnit = 'pt' {
		/**
		 * sets the FPDF measurement unit
		 *
		 * @codeCoverageIgnore
		 */
		set{
			$value = strtolower($value);

			if(in_array($value, ['cm', 'in', 'mm', 'pt'], true)){
				$this->fpdfMeasureUnit = $value;
			}

			// @todo throw or ignore silently?

		}
	}


	/*
	 * QRMarkupXML settings
	 */

	/**
	 * Sets an optional XSLT stylesheet in the XML output
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/XSLT
	 */
	public string|null $xmlStylesheet = null;


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
	 *
	 * @todo: for some reason this crashes php when trying to access the other property ($this->versionMax) within the hook
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
	 * clamp the logo space values between 0 and maximum length (177 modules at version 40)
	 */
	protected function clampLogoSpaceValue(int|null $value):int|null{

		if($value === null){
			return null;
		}

		return (int)max(0, min(177, $value));
	}

}
