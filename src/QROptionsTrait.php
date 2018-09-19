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
 */

namespace chillerlan\QRCode;

trait QROptionsTrait{

	/**
	 * QR Code version number
	 *
	 *   [1 ... 40] or QRCode::VERSION_AUTO
	 *
	 * @var int
	 */
	protected $version = QRCode::VERSION_AUTO;

	/**
	 * Minimum QR version (if $version = QRCode::VERSION_AUTO)
	 *
	 * @var int
	 */
	protected $versionMin = 1;

	/**
	 * Maximum QR version
	 *
	 * @var int
	 */
	protected $versionMax = 40;

	/**
	 * Error correct level
	 *
	 *   QRCode::ECC_X where X is
	 *    L =>  7%
	 *    M => 15%
	 *    Q => 25%
	 *    H => 30%
	 *
	 * @var int
	 */
	protected $eccLevel = QRCode::ECC_L;

	/**
	 * Mask Pattern to use
	 *
	 *  [0...7] or QRCode::MASK_PATTERN_AUTO
	 *
	 * @var int
	 */
	protected $maskPattern = QRCode::MASK_PATTERN_AUTO;

	/**
	 * Add a "quiet zone" (margin) according to the QR code spec
	 *
	 * @var bool
	 */
	protected $addQuietzone = true;

	/**
	 *  Size of the quiet zone
	 *
	 *   internally clamped to [0 ... $moduleCount / 2], defaults to 4 modules
	 *
	 * @var int
	 */
	protected $quietzoneSize = 4;

	/**
	 * QRCode::OUTPUT_MARKUP_XXXX where XXXX = HTML, SVG
	 * QRCode::OUTPUT_IMAGE_XXX where XXX = PNG, GIF, JPG
	 * QRCode::OUTPUT_STRING_XXXX where XXXX = TEXT, JSON
	 * QRCode::OUTPUT_CUSTOM
	 *
	 * @var string
	 */
	protected $outputType = QRCode::OUTPUT_IMAGE_PNG;

	/**
	 * the FQCN of the custom QROutputInterface if $outputType is set to QRCode::OUTPUT_CUSTOM
	 *
	 * @var string
	 */
	protected $outputInterface;

	/**
	 * /path/to/cache.file
	 *
	 * @var string
	 */
	protected $cachefile;

	/**
	 * newline string [HTML, SVG, TEXT]
	 *
	 * @var string
	 */
	protected $eol = PHP_EOL;

	/**
	 * size of a QR code pixel [SVG, IMAGE_*]
	 * HTML -> via CSS
	 *
	 * @var int
	 */
	protected $scale = 5;

	/**
	 * a common css class
	 *
	 * @var string
	 */
	protected $cssClass;

	/**
	 * SVG opacity
	 *
	 * @var float
	 */
	protected $svgOpacity = 1.0;

	/**
	 * anything between <defs>
	 *
	 * @see https://developer.mozilla.org/docs/Web/SVG/Element/defs
	 *
	 * @var string
	 */
	protected $svgDefs = '<style>rect{shape-rendering:crispEdges}</style>';

	/**
	 * string substitute for dark
	 *
	 * @var string
	 */
	protected $textDark = '🔴';

	/**
	 * string substitute for light
	 *
	 * @var string
	 */
	protected $textLight = '⭕';

	/**
	 * markup substitute for dark (CSS value)
	 *
	 * @var string
	 */
	protected $markupDark = '#000';

	/**
	 * markup substitute for light (CSS value)
	 *
	 * @var string
	 */
	protected $markupLight = '#fff';

	/**
	 * toggle base64 or raw image data
	 *
	 * @var bool
	 */
	protected $imageBase64 = true;

	/**
	 * toggle transparency, not supported by jpg
	 *
	 * @var bool
	 */
	protected $imageTransparent = true;

	/**
	 * @see imagecolortransparent()
	 *
	 * @var array [R, G, B]
	 */
	protected $imageTransparencyBG = [255, 255, 255];

	/**
	 * @see imagepng()
	 *
	 * @var int
	 */
	protected $pngCompression = -1;

	/**
	 * @see imagejpeg()
	 *
	 * @var int
	 */
	protected $jpegQuality = 85;

	/**
	 * Imagick output format
	 *
	 * @see Imagick::setType()
	 *
	 * @var string
	 */
	protected $imagickFormat = 'png';

	/**
	 * Imagick background color (defaults to "transparent")
	 *
	 * @see \ImagickPixel::__construct()
	 *
	 * @var string
	 */
	protected $imagickBG;

	/**
	 * Module values map
	 *
	 *   HTML, IMAGICK: #ABCDEF, cssname, rgb(), rgba()...
	 *   IMAGE: [63, 127, 255] // R, G, B
	 *
	 * @var array
	 */
	protected $moduleValues;

	/**
	 * Sets the options, called internally by the constructor
	 *
	 * @return void
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function QROptionsTrait():void{

		if(!array_key_exists($this->eccLevel, QRCode::ECC_MODES)){
			throw new QRCodeException('Invalid error correct level: '.$this->eccLevel);
		}

		if(!is_array($this->imageTransparencyBG) || count($this->imageTransparencyBG) < 3){
			$this->imageTransparencyBG = [255, 255, 255];
		}

		$this->version = (int)$this->version;

		// clamp min/max version number
		$max = $this->versionMax;
		$min = $this->versionMin;
		$this->versionMin = (int)min($min, $max);
		$this->versionMax = (int)max($min, $max);
	}

}
