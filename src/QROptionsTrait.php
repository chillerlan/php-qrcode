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
	 * SVG viewBox size. a single integer number which defines width/height of the viewBox attribute.
	 *
	 * viewBox="0 0 x x"
	 *
	 * @see https://css-tricks.com/scale-svg/#article-header-id-3
	 *
	 * @var int
	 */
	protected $svgViewBoxSize;

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
	 * set/clamp some special values, call the parent setter otherwise
	 *
	 * @param string $property
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function __set(string $property, $value):void{

		if(\in_array($property, ['eccLevel', 'maskPattern', 'imageTransparencyBG', 'version'], true)){
			$this->{'set_'.$property}($value);

			return;
		}
		elseif($property === 'versionMin'){
			$this->setMinMaxVersion($value, $this->versionMax);

			return;
		}
		elseif($property === 'versionMax'){
			$this->setMinMaxVersion($this->versionMin, $value);

			return;
		}

		parent::__set($property, $value);
	}

	/**
	 * clamp min/max version number
	 *
	 * @param int $versionMin
	 * @param int $versionMax
	 *
	 * @return void
	 */
	protected function setMinMaxVersion(int $versionMin, int $versionMax):void{
		$min = \max(1, \min(40, $versionMin));
		$max = \max(1, \min(40, $versionMax));

		$this->versionMin = \min($min, $max);
		$this->versionMax = \max($min, $max);
	}

	/**
	 * @param int $eccLevel
	 *
	 * @return void
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function set_eccLevel(int $eccLevel):void{

		if(!isset(QRCode::ECC_MODES[$eccLevel])){
			throw new QRCodeException('Invalid error correct level: '.$eccLevel);
		}

		$this->eccLevel = $eccLevel;
	}

	/**
	 * @param int $maskPattern
	 *
	 * @return void
	 */
	protected function set_maskPattern(int $maskPattern):void{

		if($maskPattern !== QRCode::MASK_PATTERN_AUTO){
			$this->maskPattern = \max(0, \min(7, $maskPattern));
		}

	}

	/**
	 * @param mixed $imageTransparencyBG
	 *
	 * @return void
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function set_imageTransparencyBG($imageTransparencyBG):void{

		// invalid value - set to white as default
		if(!\is_array($imageTransparencyBG) || \count($imageTransparencyBG) < 3){
			$this->imageTransparencyBG = [255, 255, 255];

			return;
		}

		foreach($imageTransparencyBG as $k => $v){

			if(!\is_numeric($v)){
				throw new QRCodeException('Invalid RGB value.');
			}

			// clamp the values
			$this->imageTransparencyBG[$k] = \max(0, \min(255, (int)$v));
		}

		// use the array values to not run into errors with the spread operator (...$arr)
		$this->imageTransparencyBG = \array_values($this->imageTransparencyBG);
	}

	/**
	 * @param int $version
	 *
	 * @return void
	 */
	protected function set_version(int $version):void{

		if($version !== QRCode::VERSION_AUTO){
			$this->version = \max(1, \min(40, $version));
		}

	}

}
