<?php
/**
 * php-gd realization of QR code with rounded modules
 *
 * @see https://github.com/chillerlan/php-qrcode/pull/215
 * @see https://github.com/chillerlan/php-qrcode/issues/127
 *
 * @created      17.09.2023
 * @author       livingroot
 * @copyright    2023 livingroot
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\Settings\SettingsContainerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

// --------------------
// Class definition
// --------------------

class QRGdRounded extends QRGdImagePNG{

	/** @inheritDoc */
	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){
		// enable the internal scaling for better rounding results at scale < 20
		$options->drawCircularModules = true;

		parent::__construct($options, $matrix);
	}

	/** @inheritDoc */
	protected function module(int $x, int $y, int $M_TYPE):void{

		/**
		 * The bit order (starting from 0):
		 *
		 *   0 1 2
		 *   7 # 3
		 *   6 5 4
		 */
		$neighbours = $this->matrix->checkNeighbours($x, $y);

		$x1         = ($x * $this->scale);
		$y1         = ($y * $this->scale);
		$x2         = (($x + 1) * $this->scale);
		$y2         = (($y + 1) * $this->scale);
		$rectsize   = (int)($this->scale / 2);

		$light      = $this->getModuleValue($M_TYPE);
		$dark       = $this->getModuleValue($M_TYPE | QRMatrix::IS_DARK);

		// ------------------
		// Outer rounding
		// ------------------

		if(($neighbours & (1 << 7))){ // neighbour left
			// top left
			imagefilledrectangle($this->image, $x1, $y1, ($x1 + $rectsize), ($y1 + $rectsize), $light);
			// bottom left
			imagefilledrectangle($this->image, $x1, ($y2 - $rectsize), ($x1 + $rectsize), $y2, $light);
		}

		if(($neighbours & (1 << 3))){ // neighbour right
			// top right
			imagefilledrectangle($this->image, ($x2 - $rectsize), $y1, $x2, ($y1 + $rectsize), $light);
			// bottom right
			imagefilledrectangle($this->image, ($x2 - $rectsize), ($y2 - $rectsize), $x2, $y2, $light);
		}

		if(($neighbours & (1 << 1))){ // neighbour top
			// top left
			imagefilledrectangle($this->image, $x1, $y1, ($x1 + $rectsize), ($y1 + $rectsize), $light);
			// top right
			imagefilledrectangle($this->image, ($x2 - $rectsize), $y1, $x2, ($y1 + $rectsize), $light);
		}

		if(($neighbours & (1 << 5))){ // neighbour bottom
			// bottom left
			imagefilledrectangle($this->image, $x1, ($y2 - $rectsize), ($x1 + $rectsize), $y2, $light);
			// bottom right
			imagefilledrectangle($this->image, ($x2 - $rectsize), ($y2 - $rectsize), $x2, $y2, $light);
		}

		// ---------------------
		// inner rounding
		// ---------------------

		if(!$this->matrix->check($x, $y)){

			if(($neighbours & 1) && ($neighbours & (1 << 7)) && ($neighbours & (1 << 1))){
				// top left
				imagefilledrectangle($this->image, $x1, $y1, ($x1 + $rectsize), ($y1 + $rectsize), $dark);
			}

			if(($neighbours & (1 << 1)) && ($neighbours & (1 << 2)) && ($neighbours & (1 << 3))){
				// top right
				imagefilledrectangle($this->image, ($x2 - $rectsize), $y1, $x2, ($y1 + $rectsize), $dark);
			}

			if(($neighbours & (1 << 7)) && ($neighbours & (1 << 6)) && ($neighbours & (1 << 5))){
				// bottom left
				imagefilledrectangle($this->image, $x1, ($y2 - $rectsize), ($x1 + $rectsize), $y2, $dark);
			}

			if(($neighbours & (1 << 3)) && ($neighbours & (1 << 4)) && ($neighbours & (1 << 5))){
				// bottom right
				imagefilledrectangle($this->image, ($x2 - $rectsize), ($y2 - $rectsize), $x2, $y2, $dark);
			}
		}

		imagefilledellipse(
			$this->image,
			(int)($x * $this->scale + $this->scale / 2),
			(int)($y * $this->scale + $this->scale / 2),
			($this->scale - 1),
			($this->scale - 1),
			$light
		);
	}

}


// --------------------
// Example
// --------------------

$options = new QROptions([
    'version'         => 7,
    'eccLevel'        => EccLevel::H,
    'outputType'      => QROutputInterface::CUSTOM,
    'outputInterface' => QRGdRounded::class,
    'outputBase64'    => false,
    'scale'           => 30,
    'addLogoSpace'    => true,
    'logoSpaceWidth'  => 13,
    'logoSpaceHeight' => 13,
]);


$img = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

header('Content-type: image/png');

echo $img;
