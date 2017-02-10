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
 *
 */
class QRMarkup extends QROutputAbstract{

	/**
	 * @var string
	 */
	protected $optionsInterface = QRMarkupOptions::class;

	/**
	 * @var array
	 */
	protected $types = [
		QRCode::OUTPUT_MARKUP_HTML,
		QRCode::OUTPUT_MARKUP_SVG,
	];

	/**
	 * @return string
	 */
	public function dump():string {
		switch($this->options->type){
			case QRCode::OUTPUT_MARKUP_SVG : return $this->toSVG();
			case QRCode::OUTPUT_MARKUP_HTML:
			default:
				return $this->toHTML();
		}
	}

	/**
	 * @return string
	 */
	protected function toHTML():string {
		$html = '';

		foreach($this->matrix as $row){
			// in order to not bloat the output too much, we use the shortest possible valid HTML tags
			$html .= '<'.$this->options->htmlRowTag.'>';

			foreach($row as $col){
				$tag = $col
					? 'b'  // dark
					: 'i'; // light

				$html .= '<'.$tag.'></'.$tag.'>';
			}

			if(!(bool)$this->options->htmlOmitEndTag){
				$html .= '</'.$this->options->htmlRowTag.'>';
			}

			$html .= $this->options->eol;
		}

		return $html;
	}

	/**
	 * @link https://github.com/codemasher/php-qrcode/pull/5
	 *
	 * @return string
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function toSVG():string {
		$length = $this->pixelCount * $this->options->pixelSize + $this->options->marginSize * 2;
		$class  = !empty($this->options->cssClass) ? $this->options->cssClass : hash('crc32', microtime(true));

		// svg header
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="'.$length.'" height="'.$length.'" viewBox="0 0 '.$length.' '.$length.'" style="background-color:'.$this->options->bgColor.'">'.$this->options->eol.
		       '<defs><style>.'.$class.'{fill:'.$this->options->fgColor.'} rect{shape-rendering:crispEdges}</style></defs>'.$this->options->eol;

		// svg body
		foreach($this->matrix as $r => $row){
			//we'll combine active blocks within a single row as a lightweight compression technique
			$from  = -1;
			$count = 0;

			foreach($row as $c => $pixel){
				if($pixel){
					$count++;

					if($from < 0){
						$from = $c;
					}
				}
				elseif($from >= 0){
					$svg .= '<rect x="'.($from * $this->options->pixelSize + $this->options->marginSize).'" y="'.($r * $this->options->pixelSize + $this->options->marginSize)
					        .'" width="'.($this->options->pixelSize * $count).'" height="'.$this->options->pixelSize.'" class="'.$class.'" />'.$this->options->eol;

					// reset count
					$from  = -1;
					$count = 0;
				}
			}

			// close off the row, if applicable
			if($from >= 0){
				$svg .= '<rect x="'.($from * $this->options->pixelSize + $this->options->marginSize).'" y="'.($r * $this->options->pixelSize + $this->options->marginSize)
				        .'" width="'.($this->options->pixelSize * $count).'" height="'.$this->options->pixelSize.'" class="'.$class.'" />'.$this->options->eol;
			}
		}

		// close svg
		$svg .= '</svg>'.$this->options->eol;

		// if saving to file, append the correct headers
		if($this->options->cachefile){
			$svg = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'.$this->options->eol.$svg;

			if(@file_put_contents($this->options->cachefile, $svg) === false){
				throw new QRCodeOutputException('Could not write to cache file.'); // @codeCoverageIgnore
			}
		}

		return $svg;
	}

}
