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

use chillerlan\QRCode\QRCode;

/**
 *
 */
class QRString extends QROutputAbstract{

	protected $optionsInterface = QRStringOptions::class;

	protected $types = [
		QRCode::OUTPUT_STRING_TEXT,
		QRCode::OUTPUT_STRING_JSON,
	];

	/**
	 * @return string
	 */
	public function dump():string {

		switch($this->options->type){
			case QRCode::OUTPUT_STRING_TEXT: return $this->toString();
			case QRCode::OUTPUT_STRING_JSON:
			default:
				return json_encode($this->matrix);
		}

	}

	/**
	 * @return string
	 */
	protected function toString():string {
		$str = '';

		foreach($this->matrix as $row){
			foreach($row as $col){
				$str .= $col
					? $this->options->textDark
					: $this->options->textLight;
			}

			$str .= $this->options->eol;
		}

		return $str;
	}

}
