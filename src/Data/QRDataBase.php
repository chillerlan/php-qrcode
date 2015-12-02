<?php
/**
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
use codemasher\QRCode\Data\QRDataInterface;

/**
 * Class QRDataBase
 */
class QRDataBase implements QRDataInterface{

	/**
	 * @var
	 */
	protected $mode;

	/**
	 * @var
	 */
	protected $data;

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
		$this->util = new Util;
	}

	/**
	 * @return mixed
	 */
	public function getMode(){
		return $this->mode;
	}

	/**
	 * @return mixed
	 */
	public function getData(){
		return $this->data;
	}

	/**
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function getLength(){
		throw new QRCodeException('not implemented.');
	}

	/**
	 * @param $buffer
	 *
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function write(BitBuffer &$buffer){
		throw new QRCodeException('not implemented.');
	}

	/**
	 * @param $type
	 *
	 * @return int
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function getLengthInBits($type){
		if(1 <= $type && $type < 10){

			// 1 - 9
			switch($this->mode){
				case QRConst::MODE_NUMBER   : return 10;
				case QRConst::MODE_ALPHA_NUM: return 9;
				case QRConst::MODE_8BIT_BYTE: return 8;
				case QRConst::MODE_KANJI    : return 8;
				default :
					throw new QRCodeException('mode: '.$this->mode);
			}

		}
		else if($type < 27){

			// 10 - 26
			switch($this->mode){
				case QRConst::MODE_NUMBER   : return 12;
				case QRConst::MODE_ALPHA_NUM: return 11;
				case QRConst::MODE_8BIT_BYTE: return 16;
				case QRConst::MODE_KANJI    : return 10;
				default :
					throw new QRCodeException('mode: '.$this->mode);
			}

		}
		else if($type < 41){

			// 27 - 40
			switch($this->mode){
				case QRConst::MODE_NUMBER   : return 14;
				case QRConst::MODE_ALPHA_NUM: return 13;
				case QRConst::MODE_8BIT_BYTE: return 16;
				case QRConst::MODE_KANJI    : return 12;
				default :
					throw new QRCodeException('mode: '.$this->mode);
			}

		}
		else{
			throw new QRCodeException('mode: '.$this->mode);
		}
	}

}
