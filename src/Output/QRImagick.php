<?php
/**
 * Class QRImagick
 *
 * @filesource   QRImagick.php
 * @created      04.07.2018
 * @package      chillerlan\QRCode\Output
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use Imagick, ImagickDraw, ImagickPixel;

use function is_string;

/**
 * ImageMagick output module
 * requires ext-imagick
 * @link http://php.net/manual/book.imagick.php
 * @link http://phpimagick.com
 */
class QRImagick extends QROutputAbstract{

	/**
	 * @return void
	 */
	protected function setModuleValues():void{

		foreach($this::DEFAULT_MODULE_VALUES as $type => $defaultValue){
			$v = $this->options->moduleValues[$type] ?? null;

			if(!is_string($v)){
				$this->moduleValues[$type] = $defaultValue
					? new ImagickPixel($this->options->markupDark)
					: new ImagickPixel($this->options->markupLight);
			}
			else{
				$this->moduleValues[$type] = new ImagickPixel($v);
			}
		}
	}

	/**
	 * @param string|null $file
	 *
	 * @return string
	 */
	public function dump(string $file = null):string{
		$file    = $file ?? $this->options->cachefile;
		$imagick = new Imagick;

		$imagick->newImage(
			$this->length,
			$this->length,
			new ImagickPixel($this->options->imagickBG ?? 'transparent'),
			$this->options->imagickFormat
		);

		$imageData = $this->drawImage($imagick);

		if($file !== null){
			$this->saveToFile($imageData, $file);
		}

		return $imageData;
	}

	/**
	 * @param \Imagick $imagick
	 *
	 * @return string
	 */
	protected function drawImage(Imagick $imagick):string{
		$draw = new ImagickDraw;

		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$draw->setStrokeColor($this->moduleValues[$M_TYPE]);
				$draw->setFillColor($this->moduleValues[$M_TYPE]);
				$draw->rectangle(
					$x * $this->scale,
					$y * $this->scale,
					($x + 1) * $this->scale,
					($y + 1) * $this->scale
				);

			}
		}

		$imagick->drawImage($draw);

		return (string)$imagick;
	}

}
