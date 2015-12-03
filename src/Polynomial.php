<?php
/**
 *
 * @filesource   Polynomial.php
 * @created      25.11.2015
 * @package      codemasher\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace codemasher\QRCode;

/**
 * Class Polynomial
 */
class Polynomial{

	/**
	 * @var array
	 */
	public $num = [];

	/**
	 * @var array
	 */
	protected $EXP_TABLE = [];

	/**
	 * @var array
	 */
	protected $LOG_TABLE = [];

	/**
	 * Polynomial constructor.
	 *
	 * @param array $num
	 * @param int   $shift
	 */
	public function __construct(array $num = [1], $shift = 0){
		$this->setNum($num, $shift)
		     ->setTables();
	}

	/**
	 * @param array $num
	 * @param int   $shift
	 *
	 * @return $this
	 */
	public function setNum(array $num, $shift = 0){
		$offset = 0;
		$numCount = count($num);

		while($offset < $numCount && $num[$offset] === 0){
			$offset++;
		}

		$this->num = [];
		for($i = 0; $i < $numCount - $offset + $shift; $i++){
			$this->num[] = 0;
		}

		for($i = 0; $i < $numCount - $offset; $i++){
			$this->num[$i] = $num[$i + $offset];
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	protected function setTables(){
		$numArr = [];
		for($i = 0; $i < 256; $i++){
			$numArr[] = 0;
		}

		$this->EXP_TABLE = $this->LOG_TABLE = $numArr;

		for($i = 0; $i < 8; $i++){
			$this->EXP_TABLE[$i] = 1 << $i;
		}

		for($i = 8; $i < 256; $i++){
			$this->EXP_TABLE[$i] = $this->EXP_TABLE[$i - 4] ^ $this->EXP_TABLE[$i - 5] ^ $this->EXP_TABLE[$i - 6] ^ $this->EXP_TABLE[$i - 8];
		}

		for($i = 0; $i < 255; $i++){
			$this->LOG_TABLE[$this->EXP_TABLE[$i]] = $i;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString(){
		$buffer = '';

		foreach($this->num as $i => $value){
			if($i > 0){
				$buffer .= ',';
			}
			$buffer .= $value;
		}

		return $buffer;
	}

	/**
	 * @return string
	 */
	public function toLogString(){
		$buffer = '';

		foreach($this->num as $i => $value){
			if($i > 0){
				$buffer .= ',';
			}
			$buffer .= $this->glog($value);
		}

		return $buffer;
	}

	/**
	 * @param array $e
	 *
	 * @return $this
	 */
	public function multiply(array $e){

		$num = [];
		$len = count($this->num) + count($e) - 1;
		for($i = 0; $i < $len; $i++){
			$num[] = 0;
		}

		foreach($this->num as $i => $vi){
			foreach($e as $j => $vj){
				$num[$i + $j] ^= $this->gexp($this->glog($vi) + $this->glog($vj));
			}
		}

		$this->setNum($num);

		return $this;
	}

	/**
	 * @param array $e
	 *
	 * @return $this
	 */
	public function mod($e){
#		$starttime = microtime(true);

		if(count($this->num) - count($e) < 0){
			return $this;
		}

		$ratio = $this->glog($this->num[0]) - $this->glog($e[0]);

		$num = [];
		foreach($this->num as $i => $value){
			$num[$i] = $value;
		}

		foreach($e as $i => $value){
			$num[$i] ^= $this->gexp($this->glog($e[$i]) + $ratio);
		}

		$this->setNum($num)->mod($e);

#		echo 'Polynomial::mod '.round((microtime(true)-$starttime), 5).PHP_EOL;

		return $num;
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

		return $this->LOG_TABLE[$n];
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

		return $this->EXP_TABLE[$n];
	}

}
