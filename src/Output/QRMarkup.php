<?php
/**
 * Class QRMarkup
 *
 * @filesource   QRMarkup.php
 * @created      17.12.2016
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\QRCode;

/**
 * Converts the matrix into markup types: HTML, SVG, ...
 */
class QRMarkup extends QROutputAbstract{

	protected $defaultMode = QRCode::OUTPUT_MARKUP_SVG;

	/**
	 * @return string|bool
	 */
	protected function html(){
		$html = '';

		foreach($this->matrix->matrix() as $row){
			$html .= '<div>';

			foreach($row as $pixel){
				$html .= '<span style="background: '.($this->options->moduleValues[$pixel] ?: 'lightgrey').';"></span>';
			}

			$html .= '</div>'.$this->options->eol;
		}

		if($this->options->cachefile){
			return '<!DOCTYPE html><head><meta charset="UTF-8"></head><body>'.$this->options->eol.$html.'</body>';
		}

		return $html;
	}

	/**
	 * @link https://github.com/codemasher/php-qrcode/pull/5
	 *
	 * @return string|bool
	 */
	protected function svg(){
		$scale  = $this->options->scale;
		$length = $this->moduleCount * $scale;
		$matrix = $this->matrix->matrix();

		$svg = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="'.$length.'px" height="'.$length.'px">'
		       .$this->options->eol
		       .'<defs>'.$this->options->svgDefs.'</defs>'
		       .$this->options->eol;

		foreach($this->options->moduleValues as $M_TYPE => $value){

			// fallback
			if(is_bool($value)){
				$value = $value ? '#000' : '#fff';
			}

			$path = '';

			foreach($matrix as $y => $row){
				//we'll combine active blocks within a single row as a lightweight compression technique
				$start = null;
				$count = 0;

				foreach($row as $x => $module){

					if($module === $M_TYPE){
						$count++;

						if($start === null){
							$start = $x * $scale;
						}

						if($row[$x + 1] ?? false){
							continue;
						}
					}

					if($count > 0){
						$len = $count * $scale;
						$path .= 'M' .$start. ' ' .($y * $scale). ' h'.$len.' v'.$scale.' h-'.$len.'Z ';

						// reset count
						$count = 0;
						$start = null;
					}

				}

			}

			if(!empty($path)){
				$svg .= '<path class="qr-'.$M_TYPE.' '.$this->options->cssClass.'" stroke="transparent" fill="'.$value.'" fill-opacity="'.$this->options->svgOpacity.'" d="'.$path.'" />';
			}

		}

		// close svg
		$svg .= '</svg>'.$this->options->eol;

		// if saving to file, append the correct headers
		if($this->options->cachefile){
			return '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'.$this->options->eol.$svg;
		}

		return $svg;
	}

}
