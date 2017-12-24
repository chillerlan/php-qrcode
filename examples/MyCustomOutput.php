<?php
/**
 * Class MyCustomOutput
 *
 * @filesource   MyCustomOutput.php
 * @created      24.12.2017
 * @package      chillerlan\QRCodeExamples
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeExamples;

use chillerlan\QRCode\Output\QROutputAbstract;

/**
 */
class MyCustomOutput extends QROutputAbstract{

	public function dump(){

		$output = '';

		for($row = 0; $row < $this->moduleCount; $row++){
			for($col = 0; $col < $this->moduleCount; $col++){
				$output .= (int)$this->matrix->check($col, $row);
			}
		}

		return $output;
	}

}
