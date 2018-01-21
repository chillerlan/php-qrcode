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

	protected $defaultMode = QRCode::OUTPUT_STRING_TEXT;

	/**
	 * @return string
	 */
	protected function text():string{
		$str = [];

		foreach($this->matrix->matrix() as $row){
			$r = [];

			foreach($row as $col){
				$col = $this->options->moduleValues[$col];

				// fallback
				if(is_bool($col) || !is_string($col)){
					$col = $col
						? $this->options->textDark
						: $this->options->textLight;
				}

				$r[] = $col;
			}

			$str[] = implode('', $r);
		}

		return implode($this->options->eol, $str);
	}

	/**
	 * @return string
	 */
	protected function json():string{
		return json_encode($this->matrix->matrix());
	}

}
