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

use chillerlan\QRCode\QRCode;

/**
 *
 */
class QRImage extends QROutputAbstract{

	/**
	 * @todo
	 * @return string
	 */
	public function dump():string {
		$length     = ($this->moduleCount + ($this->options->addQuietzone ? 8 : 0)) * $this->options->scale;
		$image      = imagecreatetruecolor($length, $length);
		$background = imagecolorallocate($image, 255, 255, 255);

		if((bool)$this->options->imageTransparent && $this->options->outputType !== QRCode::OUTPUT_IMAGE_JPG){
			imagecolortransparent($image, $background);
		}

		imagefilledrectangle($image, 0, 0, $length, $length, $background);

		foreach($this->matrix->matrix() as $r => $row){
			foreach($row as $c => $pixel){
				list($red, $green, $blue) = $this->options->moduleValues[$pixel];

				imagefilledrectangle(
					$image,
					 $c      * $this->options->scale,
					 $r      * $this->options->scale,
					($c + 1) * $this->options->scale - 1,
					($r + 1) * $this->options->scale - 1,
					imagecolorallocate($image, $red, $green, $blue)
				);

			}
		}

		ob_start();

		$this->{$this->options->outputType ?? 'png'}($image);
		$imageData = ob_get_contents();
		imagedestroy($image);

		ob_end_clean();

		if((bool)$this->options->imageBase64){
			$imageData = 'data:image/'.$this->options->outputType.';base64,'.base64_encode($imageData);
		}

		return $imageData;
	}

	protected function png(&$image){
		imagepng(
			$image,
			$this->options->cachefile,
			in_array($this->options->pngCompression, range(-1, 9), true)
				? $this->options->pngCompression
				: -1
		);

	}

	/**
	 * Jiff - like... JitHub!
	 */
	protected function gif(&$image){
		imagegif($image, $this->options->cachefile);
	}

	protected function jpg(&$image){
		imagejpeg(
			$image,
			$this->options->cachefile,
			in_array($this->options->jpegQuality, range(0, 100), true)
				? $this->options->jpegQuality
				: 85
		);
	}

}
