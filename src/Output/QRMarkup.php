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
	 * @var string
	 */
	protected $defaultMode = QRCode::OUTPUT_MARKUP_SVG;

	/**
	 * @see \sprintf()
	 *
	 * @var string
	 */
	protected $svgHeader = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="qr-svg %1$s" style="width: 100%%; height: auto;" viewBox="0 0 %2$d %2$d">';

	/**
	 * @return void
	 */
	protected function setModuleValues():void{

		foreach($this::DEFAULT_MODULE_VALUES as $M_TYPE => $defaultValue){
			$v = $this->options->moduleValues[$M_TYPE] ?? null;

			if(!\is_string($v)){
				$this->moduleValues[$M_TYPE] = $defaultValue
					? $this->options->markupDark
					: $this->options->markupLight;
			}
			else{
				$this->moduleValues[$M_TYPE] = \trim(\strip_tags($v), '\'"');
			}

		}

	}

	/**
	 * @return string
	 */
	protected function html():string{
		$html = '<div class="'.$this->options->cssClass.'">'.$this->options->eol;

		foreach($this->matrix->matrix() as $row){
			$html .= '<div>';

			foreach($row as $M_TYPE){
				$html .= '<span style="background: '.$this->moduleValues[$M_TYPE].';"></span>';
			}

			$html .= '</div>'.$this->options->eol;
		}

		$html .= '</div>'.$this->options->eol;

		if($this->options->cachefile){
			return '<!DOCTYPE html><head><meta charset="UTF-8"></head><body>'.$this->options->eol.$html.'</body>';
		}

		return $html;
	}

	/**
	 * @link https://github.com/codemasher/php-qrcode/pull/5
	 *
	 * @return string
	 */
	protected function svg():string{
		$matrix = $this->matrix->matrix();

		$svg = \sprintf($this->svgHeader, $this->options->cssClass, $this->options->svgViewBoxSize ?? $this->moduleCount)
		       .$this->options->eol
		       .'<defs>'.$this->options->svgDefs.'</defs>'
		       .$this->options->eol;

		foreach($this->moduleValues as $M_TYPE => $value){
			$path = '';

			foreach($matrix as $y => $row){
				//we'll combine active blocks within a single row as a lightweight compression technique
				$start = null;
				$count = 0;

				foreach($row as $x => $module){

					if($module === $M_TYPE){
						$count++;

						if($start === null){
							$start = $x;
						}

						if($row[$x + 1] ?? false){
							continue;
						}
					}

					if($count > 0){
						$len = $count;
						$path .= 'M' .$start. ' ' .$y. ' h'.$len.' v1 h-'.$len.'Z ';

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
