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

		// clamp input (determine sane values!)
		$this->options->pixelSize = max(1, min(25, (int)$this->options->pixelSize));
		$this->options->marginSize = max(0, min(25, (int)$this->options->marginSize));

		foreach(['fgRed', 'fgGreen', 'fgBlue', 'bgRed', 'bgGreen', 'bgBlue'] as $val){
			$this->options->{$val} = max(0, min(255, (int)$this->options->{$val}));
		}

	}

	/**
	 * @return string
	 */
	public function dump(){
		// svg doesn't need all this GD business
		if($this->options->type === QRCode::OUTPUT_IMAGE_SVG){
			return $this->toSVG();
		}

		$length     = $this->pixelCount * $this->options->pixelSize + $this->options->marginSize * 2;
		$image      = imagecreatetruecolor($length, $length);
		$foreground = imagecolorallocate($image, $this->options->fgRed, $this->options->fgGreen, $this->options->fgBlue);
		$background = imagecolorallocate($image, $this->options->bgRed, $this->options->bgGreen, $this->options->bgBlue);

		if((bool)$this->options->transparent && $this->options->type !== QRCode::OUTPUT_IMAGE_JPG){
			imagecolortransparent($image, $background);
		}

		imagefilledrectangle($image, 0, 0, $length, $length, $background);

		foreach($this->matrix as $r => $row){
			foreach($row as $c => $pixel){
				if($pixel){
					imagefilledrectangle($image,
						$this->options->marginSize +  $c      * $this->options->pixelSize,
						$this->options->marginSize +  $r      * $this->options->pixelSize,
						$this->options->marginSize + ($c + 1) * $this->options->pixelSize - 1,
						$this->options->marginSize + ($r + 1) * $this->options->pixelSize - 1,
						$foreground);
				}
			}
		}

		ob_start();

		switch($this->options->type){
			case QRCode::OUTPUT_IMAGE_JPG:
				imagejpeg(
					$image,
					$this->options->cachefile,
					in_array($this->options->jpegQuality, range(0, 100), true)
						? $this->options->jpegQuality
						: 85
				);
				break;
			case QRCode::OUTPUT_IMAGE_GIF: /** Actually, it's pronounced "DJIFF". *hides* */
				imagegif(
					$image,
					$this->options->cachefile
				);
				break;
			case QRCode::OUTPUT_IMAGE_PNG:
			default:
				imagepng(
					$image,
					$this->options->cachefile,
					in_array($this->options->pngCompression, range(-1, 9), true)
						? $this->options->pngCompression
						: -1
				);
		}

		$imageData = ob_get_contents();
		imagedestroy($image);
		ob_end_clean();

		if((bool)$this->options->base64){
			$imageData = 'data:image/'.$this->options->type.';base64,'.base64_encode($imageData);
		}

		return $imageData;
	}

	/**
	 * @return string
	 */
	protected function toSVG(){
		$length     = $this->pixelCount * $this->options->pixelSize + $this->options->marginSize * 2;
		$class      = 'f' . hash('crc32', microtime(true));
		$foreground = 'rgb(' . $this->options->fgRed . ',' . $this->options->fgGreen . ',' . $this->options->fgBlue . ')';
		$background = (bool)$this->options->transparent
			? 'transparent'
			: 'rgb(' . $this->options->bgRed . ',' . $this->options->bgGreen . ',' . $this->options->bgBlue . ')';

		ob_start();

		// svg header
		echo '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="' . $length . '" height="' . $length . '" viewBox="0 0 ' . $length . ' ' . $length . '" style="background-color:' . $background . '"><defs><style>.' . $class . '{fill:' . $foreground . '} rect{shape-rendering:crispEdges}</style></defs>';

		// svg body
		foreach($this->matrix AS $r=>$row){
			//we'll combine active blocks within a single row as a lightweight compression technique
			$from = -1;
			$count = 0;

			foreach($row AS $c=>$pixel){
				if($pixel){
					$count++;
					if($from < 0)
						$from = $c;
				}
				else if($from >= 0){
					echo '<rect x="' . ($from * $this->options->pixelSize + $this->options->marginSize) . '" y="' . ($r * $this->options->pixelSize + $this->options->marginSize) . '" width="' . ($this->options->pixelSize * $count) . '" height="' . $this->options->pixelSize . '" class="' . $class . '" />';

					// reset count
					$from = -1;
					$count = 0;
				}
			}

			// close off the row, if applicable
			if($from >= 0){
				echo '<rect x="' . ($from * $this->options->pixelSize + $this->options->marginSize) . '" y="' . ($r * $this->options->pixelSize + $this->options->marginSize) . '" width="' . ($this->options->pixelSize * $count) . '" height="' . $this->options->pixelSize . '" class="' . $class . '" />';
			}
		}

		// close svg
		echo '</svg>';
		$imageData = ob_get_clean();

		// if saving to file, append the correct headers
		if($this->options->cachefile){
			@file_put_contents($this->options->cachefile, '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . "\n" . $imageData);
		}

		if((bool)$this->options->base64){
			$imageData = 'data:image/svg+xml;base64,'.base64_encode($imageData);
		}

		return $imageData;
	}

}
