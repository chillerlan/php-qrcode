<?php
/**
 * Class QRImage
 *
 * @filesource   QRImage.php
 * @created      05.12.2015
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;
use chillerlan\QRCode\Util;

/**
 * toBase64()
 */
class QRImage extends QROutputBase implements QROutputInterface{

	/**
	 * @var \chillerlan\QRCode\Output\QRImageOptions $outputOptions
	 */
	protected $options;

	/**
	 * @var \chillerlan\QRCode\Output\QRImageOptions $outputOptions
	 */
	public function __construct(QRImageOptions $outputOptions = null){
		$this->options = $outputOptions;

		if(!$this->options instanceof QRImageOptions){
			$this->options = new QRImageOptions;
		}

	}



	/**
	 * added $fg (foreground), $bg (background), and $bgtrans (use transparent bg) parameters
	 * also added some simple error checking on parameters
	 * updated 2015.07.27 ~ DoktorJ
	 *
	 * @param int        $size
	 * @param int        $margin
	 * @param int        $fg
	 * @param int        $bg
	 * @param bool|false $bgtrans
	 *
	 * @return resource
	 */
	public function toImage($size = 2, $margin = 2, $fg = 0x000000, $bg = 0xFFFFFF, $bgtrans = false){

		// size/margin EC
		if(!is_numeric($size)){
			$size = 2;
		}
		if(!is_numeric($margin)){
			$margin = 2;
		}
		if($size < 1){
			$size = 1;
		}
		if($margin < 0){
			$margin = 0;
		}

		$image_size = $this->pixelCount * $size + $margin * 2;

		$image = imagecreatetruecolor($image_size, $image_size);

		// fg/bg EC
		if($fg < 0 || $fg > 0xFFFFFF){
			$fg = 0x0;
		}
		if($bg < 0 || $bg > 0xFFFFFF){
			$bg = 0xFFFFFF;
		}

		// convert hexadecimal RGB to arrays for imagecolorallocate
		$fgrgb = Util::hex2rgb($fg);
		$bgrgb = Util::hex2rgb($bg);

		// replace $black and $white with $fgc and $bgc
		$fgc = imagecolorallocate($image, $fgrgb['r'], $fgrgb['g'], $fgrgb['b']);
		$bgc = imagecolorallocate($image, $bgrgb['r'], $bgrgb['g'], $bgrgb['b']);
		if($bgtrans){
			imagecolortransparent($image, $bgc);
		}

		// update $white to $bgc
		imagefilledrectangle($image, 0, 0, $image_size, $image_size, $bgc);

		for($r = 0; $r < $this->pixelCount; $r++){
			for($c = 0; $c < $this->pixelCount; $c++){
				if($this->matrix[$r][$c]){

					// update $black to $fgc
					imagefilledrectangle($image,
						$margin + $c * $size,
						$margin + $r * $size,
						$margin + ($c + 1) * $size - 1,
						$margin + ($r + 1) * $size - 1,
						$fgc);
				}
			}
		}

		return $image;
	}

	public function dump(){
		return $this->toImage();
	}

	protected function toPNG(){

	}

	/**
	 * Actually, it's pronounced "DJIFF". *hides*
	 */
	protected function toGIF(){

	}
}
