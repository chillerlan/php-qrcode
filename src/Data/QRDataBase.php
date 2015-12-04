<?php
/**
 * Class QRDataBase
 *
 * @filesource   QRDataBase.php
 * @created      25.11.2015
 * @package      codemasher\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode\Data;

use codemasher\QRCode\BitBuffer;
use codemasher\QRCode\QRCodeException;

/**
 *
 */
class QRDataBase implements QRDataInterface{

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
	 * QRDataBase constructor.
	 *
	 * @param $data
	 */
	public function __construct($data){
		$this->data = $data;
		$this->dataLength = strlen($data);
	}

	/**
	 * @param $type
	 *
	 * @return int
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function getLengthInBits($type){

		switch(true){
			case $type >= 1 && $type <= 9: return $this->lengthBits[0]; // 1 - 9
			case $type <= 26             : return $this->lengthBits[1]; // 10 - 26
			case $type <= 40             : return $this->lengthBits[2]; // 27 - 40
			default:
				throw new QRCodeException('$type: '.$type);
		}

	}

	/**
	 * @todo  Implement write() method.
	 *
	 * @param \codemasher\QRCode\BitBuffer $buffer
	 *
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function write(BitBuffer &$buffer){
		throw new QRCodeException('Method should be implemented in child classes');
	}

}
