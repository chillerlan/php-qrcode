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

use function is_string, sprintf, strip_tags, trim;

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
	 * @inheritDoc
	 */
	protected function setModuleValues():void{

		foreach($this::DEFAULT_MODULE_VALUES as $M_TYPE => $defaultValue){
			$v = $this->options->moduleValues[$M_TYPE] ?? null;

			if(!is_string($v)){
				$this->moduleValues[$M_TYPE] = $defaultValue
					? $this->options->markupDark
					: $this->options->markupLight;
			}
			else{
				$this->moduleValues[$M_TYPE] = trim(strip_tags($v), '\'"');
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

		$svg = sprintf($this->svgHeader, $this->options->cssClass, $this->options->svgViewBoxSize ?? $this->moduleCount)
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

						if(isset($row[$x + 1])){
							continue;
						}
					}

					if($count > 0){
						$len = $count;
						$path .= sprintf('M%s %s h%s v1 h-%sZ ', $start, $y, $len, $len);

						// reset count
						$count = 0;
						$start = null;
					}

				}

			}

			if(!empty($path)){
				$svg .= sprintf('<path class="qr-%s %s" stroke="transparent" fill="%s" fill-opacity="%s" d="%s" />', $M_TYPE, $this->options->cssClass, $value, $this->options->svgOpacity, $path);
			}

		}

		// close svg
		$svg .= '</svg>'.$this->options->eol;

		// if saving to file, append the correct headers
		if($this->options->cachefile){
			return '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'.$this->options->eol.$svg;
		}

		if($this->options->imageBase64){
			$svg = sprintf('data:image/svg+xml;base64,%s', base64_encode($svg));
		}

		return $svg;
	}

}
