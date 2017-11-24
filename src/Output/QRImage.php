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
 * Converts the matrix into images, raw or base64 output
 */
class QRImage extends QROutputAbstract{

	/**
	 * @return string
	 */
	public function dump():string{
		$scale      = $this->options->scale;
		$length     = $this->moduleCount * $scale;
		$image      = imagecreatetruecolor($length, $length);
		$background = imagecolorallocate($image, ...$this->options->imageTransparencyBG);

		if((bool)$this->options->imageTransparent && in_array($this->options->outputType, [QRCode::OUTPUT_IMAGE_PNG, QRCode::OUTPUT_IMAGE_GIF,], true)){
			imagecolortransparent($image, $background);
		}

		imagefilledrectangle($image, 0, 0, $length, $length, $background);

		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $pixel){
				$color = imagecolorallocate($image, ...$this->options->moduleValues[$pixel]);

				imagefilledrectangle($image, $x * $scale, $y * $scale, ($x + 1) * $scale - 1, ($y + 1) * $scale - 1, $color);
			}
		}

		ob_start();

		call_user_func_array([$this, $this->options->outputType ?? QRCode::OUTPUT_IMAGE_PNG], [&$image]);

		$imageData = ob_get_contents();
		imagedestroy($image);

		ob_end_clean();

		if((bool)$this->options->imageBase64){
			$imageData = 'data:image/'.$this->options->outputType.';base64,'.base64_encode($imageData);
		}

		return $imageData;
	}

	/**
	 * @param $image
	 */
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
	 *
	 * @param $image
	 */
	protected function gif(&$image){
		imagegif($image, $this->options->cachefile);
	}

	/**
	 * @param $image
	 */
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
