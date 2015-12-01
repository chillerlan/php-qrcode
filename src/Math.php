<?php
/**
 *
 * @filesource   Math.php
 * @created      25.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode;

use codemasher\QRCode\QRCodeException;

/**
 * Class Math
 */
class Math{

	/**
	 * @var array
	 */
	protected $QR_MATH_EXP_TABLE;

	/**
	 * @var array
	 */
	protected $QR_MATH_LOG_TABLE;

	/**
	 * Math constructor.
	 */
	public function __construct(){
		$this->QR_MATH_EXP_TABLE = $this->createNumArray(256);

		for($i = 0; $i < 8; $i++){
			$this->QR_MATH_EXP_TABLE[$i] = 1 << $i;
		}

		for($i = 8; $i < 256; $i++){
			$this->QR_MATH_EXP_TABLE[$i] =
				$this->QR_MATH_EXP_TABLE[$i - 4]
				^$this->QR_MATH_EXP_TABLE[$i - 5]
				^$this->QR_MATH_EXP_TABLE[$i - 6]
				^$this->QR_MATH_EXP_TABLE[$i - 8];
		}

		$this->QR_MATH_LOG_TABLE = $this->createNumArray(256);

		for($i = 0; $i < 255; $i++){
			$this->QR_MATH_LOG_TABLE[$this->QR_MATH_EXP_TABLE[$i]] = $i;
		}
	}

	/**
	 * @param int $length
	 *
	 * @return array
	 */
	public function createNumArray($length){
		$num_array = [];

		for($i = 0; $i < $length; $i++){
			$num_array[] = 0;
		}

		return $num_array;
	}

	/**
	 * @param int $n
	 *
	 * @return mixed
	 * @throws \codemasher\QRCode\QRCodeException
	 */
	public function glog($n){

		if($n < 1){
			throw new QRCodeException('log('.$n.')');
		}

		return $this->QR_MATH_LOG_TABLE[$n];
	}

	/**
	 * @param int $n
	 *
	 * @return mixed
	 */
	public function gexp($n){

		while($n < 0){
			$n += 255;
		}

		while($n >= 256){
			$n -= 255;
		}

		return $this->QR_MATH_EXP_TABLE[$n];
	}

}
