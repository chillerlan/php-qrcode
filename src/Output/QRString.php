<?php
/**
 * Class QRString
 *
 * @filesource   QRString.php
 * @created      05.12.2015
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;
use chillerlan\QRCode\QRConst;

/**
 *
 */
class QRString extends QROutputBase implements QROutputInterface{

	/**
	 * @var \chillerlan\QRCode\Output\QRStringOptions
	 */
	protected $options;

	/**
	 * @var \chillerlan\QRCode\Output\QRStringOptions $outputOptions
	 */
	public function __construct(QRStringOptions $outputOptions = null){
		$this->options = $outputOptions;

		if(!$this->options instanceof QRStringOptions){
			$this->options = new QRStringOptions;
		}

	}

	/**
	 * @return string
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function dump(){

		switch($this->options->type){
			case QRConst::OUTPUT_STRING_TEXT: return $this->toText();
			case QRConst::OUTPUT_STRING_JSON: return $this->toJSON();
			case QRConst::OUTPUT_STRING_HTML: return $this->toHTML();
			default:
				throw new QRCodeOutputException('Invalid string output type!');
		}

	}

	/**
	 * @return string
	 */
	public function toText(){
		$text = '';

		foreach($this->matrix as &$row){
			foreach($row as &$col){
				$text .= $col
					? $this->options->textDark
					: $this->options->textLight;
			}

			$text .= $this->options->textNewline;
		}

		return $text;
	}

	/**
	 * @return string
	 */
	public function toJSON(){
		return json_encode($this->matrix);
	}

	/**
	 * @return string
	 */
	public function toHTML(){
		$html = '';

		foreach($this->matrix as &$row){
			// in order to not bloat the output too much, we use the shortest possible valid HTML tags
			$html .= '<p>';
			foreach($row as &$col){
				$tag = $col
					? 'b'  // dark
					: 'i'; // light

				$html .= '<'.$tag.'></'.$tag.'>';
			}

			// the closing <p> tag may be omitted
#			$html .= '</p>';
			$html .= PHP_EOL;
		}

		return $html;
	}

}
