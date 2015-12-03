<?php
/**
 * Class QRDataBase
 *
 * @filesource   QRDataBase.php
 * @created      25.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode\Data;

use codemasher\QRCode\BitBuffer;
use codemasher\QRCode\QRConst;
use codemasher\QRCode\QRCodeException;
use codemasher\QRCode\Util;

/**
 *
 */
class QRDataBase{

	/**
	 * @var
	 */
	public $mode;

	/**
	 * @var string
	 */
	public $data;

	/**
	 * @var int
	 */
	public $dataLength;

	/**
	 * @var array
	 */
	protected $lengthBits = [0, 0, 0];

	/**
	 * @var \codemasher\QRCode\Util
	 */
	protected $util;

	/**
	 * QRDataBase constructor.
	 *
	 * @param $data
	 */
	public function __construct($data){
		$this->data = $data;
		$this->dataLength = strlen($data);
		$this->util = new Util;
	}

	/**
	 * @param $type
	 *
	 * @return int
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function getLengthInBits($type){

		switch(true){
			case 1 <= $type && $type < 10: return $this->lengthBits[0]; break; // 1 - 9
			case $type < 27: return  $this->lengthBits[1]; break; // 10 - 26
			case $type < 41: return  $this->lengthBits[2]; break; // 27 - 40
			default:
				throw new QRCodeException('mode: '.$this->mode);
		}

	}

}
