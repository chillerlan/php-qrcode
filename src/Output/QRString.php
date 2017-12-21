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
 * Converts the matrix data into string types
 */
class QRString extends QROutputAbstract{

	/**
	 * @return string
	 */
	public function dump():string{

		switch($this->options->outputType){
			case QRCode::OUTPUT_STRING_TEXT:
				return $this->toString();
			case QRCode::OUTPUT_STRING_JSON:
			default:
				return json_encode($this->matrix->matrix());
		}

	}

	/**
	 * @return string
	 */
	protected function toString():string{
		$str = '';

		foreach($this->matrix->matrix() as $row){
			foreach($row as $col){
				$col = $this->options->moduleValues[$col];
				
				// fallback
				if(is_bool($col) || !is_string($col)){
					$col = $col ? $this->options->textDark : $this->options->textLight;
				}


				$str .= $col;
			}

			$str .= $this->options->eol;
		}

		return $str;
	}

}
