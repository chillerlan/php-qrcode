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

	/**
	 * @return string
	 */
	public function dump(){

		if($this->options->cachefile !== null && !is_writable(dirname($this->options->cachefile))){
			throw new QRCodeOutputException('Could not write data to cache file: '.$this->options->cachefile);
		}

		$data = $this->options->outputType === QRCode::OUTPUT_MARKUP_HTML
			? $this->toHTML()
			: $this->toSVG();

		if($this->options->cachefile !== null){
			$this->saveToFile($data);
		}

		return $data;
	}

	/**
	 * @return string|bool
	 */
	protected function toHTML(){
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
	protected function toSVG(){
		$length = ($this->moduleCount + ($this->options->addQuietzone ? 8 : 0)) * $this->options->scale;

		// svg header
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="'.$length.'" height="'.$length.'" viewBox="0 0 '.$length.' '.$length.'">'.$this->options->eol.
		       '<defs><style>rect{shape-rendering:crispEdges}</style></defs>'.$this->options->eol;

		// @todo: optimize -> see https://github.com/alexeyten/qr-image/blob/master/lib/vector.js
		foreach($this->options->moduleValues as $key => $value){

			// fallback
			if(is_bool($value)){
				$value = $value ? '#000' : '#fff';
			}

			// svg body
			foreach($this->matrix->matrix() as $y => $row){
				//we'll combine active blocks within a single row as a lightweight compression technique
				$from  = -1;
				$count = 0;

				foreach($row as $x => $pixel){
					if($pixel === $key){
						$count++;

						if($from < 0){
							$from = $x;
						}
					}
					elseif($from >= 0){
						$svg .= '<rect x="'.($from * $this->options->scale).'" y="'.($y * $this->options->scale)
						        .'" width="'.($this->options->scale * $count).'" height="'.$this->options->scale.'" fill="'.$value.'"'
						        .(trim($this->options->cssClass) !== '' ? ' class="'.$this->options->cssClass.'"' :'').' />'
						        .$this->options->eol;

						// reset count
						$from  = -1;
						$count = 0;
					}
				}

				// close off the row, if applicable
				if($from >= 0){
					$svg .= '<rect x="'.($from * $this->options->scale).'" y="'.($y * $this->options->scale)
					        .'" width="'.($this->options->scale * $count).'" height="'.$this->options->scale.'" class="'.$this->options->cssClass.'" fill="'.$value.'" />'.$this->options->eol;
				}
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
