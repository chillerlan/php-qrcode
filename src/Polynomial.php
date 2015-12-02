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

use codemasher\QRCode\Math;

/**
 * Class Polynomial
 */
class Polynomial{

	/**
	 * @var
	 */
	protected $num;

	/**
	 * @var \codemasher\QRCode\Math
	 */
	protected $math;

	/**
	 * Polynomial constructor.
	 *
	 * @param array $num
	 * @param int   $shift
	 */
	public function __construct(array $num, $shift = 0){
		$this->math = new Math;

		$offset = 0;
		while($offset < count($num) && $num[$offset] === 0){
			$offset++;
		}

		$this->num = $this->math->createNumArray(count($num) - $offset + $shift);
		for($i = 0; $i < count($num) - $offset; $i++){
			$this->num[$i] = $num[$i + $offset];
		}
	}

	/**
	 * @param $index
	 * @todo rename!
	 *
	 * @return mixed
	 */
	public function getNum($index){
		return $this->num[$index];
	}

	/**
	 * @return int
	 */
	public function getLength(){
		return count($this->num);
	}

	/**
	 * PHP5
	 * @return string
	 */
	public function __toString(){
		return $this->toString();
	}

	/**
	 * @return string
	 */
	public function toString(){
		$buffer = '';

		for($i = 0; $i < $this->getLength(); $i++){
			if($i > 0){
				$buffer .= ',';
			}
			$buffer .= $this->getNum($i);
		}

		return $buffer;
	}

	/**
	 * @return string
	 */
	public function toLogString(){
		$buffer = '';

		for($i = 0; $i < $this->getLength(); $i++){
			if($i > 0){
				$buffer .= ',';
			}
			$buffer .= $this->math->glog($this->getNum($i));
		}

		return $buffer;
	}

	/**
	 * @param \codemasher\QRCode\Polynomial $e
	 *
	 * @return \codemasher\QRCode\Polynomial
	 */
	public function multiply($e){
		$num = $this->math->createNumArray($this->getLength() + $e->getLength() - 1);

		for($i = 0; $i < $this->getLength(); $i++){
			for($j = 0; $j < $e->getLength(); $j++){
				$num[$i + $j] ^= $this->math->gexp($this->math->glog($this->getNum($i)) + $this->math->glog($e->getNum($j)));
			}
		}

		return new Polynomial($num);
	}

	/**
	 * @param \codemasher\QRCode\Polynomial $e
	 *
	 * @return $this|\codemasher\QRCode\Polynomial
	 */
	public function mod($e){

		if($this->getLength() - $e->getLength() < 0){
			return $this;
		}

		$ratio = $this->math->glog($this->getNum(0)) - $this->math->glog($e->getNum(0));

		$num = $this->math->createNumArray($this->getLength());
		for($i = 0; $i < $this->getLength(); $i++){
			$num[$i] = $this->getNum($i);
		}

		for($i = 0; $i < $e->getLength(); $i++){
			$num[$i] ^= $this->math->gexp($this->math->glog($e->getNum($i)) + $ratio);
		}

		return (new Polynomial($num))->mod($e);
	}

}
