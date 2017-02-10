<?php
/**
 * Class Polynomial
 *
 * @filesource   Polynomial.php
 * @created      25.11.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

/**
 *
 */
class Polynomial{

	/**
	 * @var array
	 */
	public $num = [];

	/**
	 * @var array
	 */
	protected $expTable = [];

	/**
	 * @var array
	 */
	protected $logTable = [];

	/**
	 * Polynomial constructor.
	 *
	 * @param array $num
	 * @param int   $shift
	 */
	public function __construct(array $num = [1], int $shift = 0){
		$this->setNum($num, $shift)->setTables();
	}

	/**
	 * @param array $num
	 * @param int   $shift
	 *
	 * @return \chillerlan\QRCode\Polynomial
	 */
	public function setNum(array $num, int $shift = 0):Polynomial {
		$offset = 0;
		$numCount = count($num);

		while($offset < $numCount && $num[$offset] === 0){
			$offset++;
		}

		$this->num = array_fill(0, $numCount - $offset + $shift, 0);

		for($i = 0; $i < $numCount - $offset; $i++){
			$this->num[$i] = $num[$i + $offset];
		}

		return $this;
	}

	/**
	 * @return void
	 */
	protected function setTables(){
		$this->expTable = $this->logTable = array_fill(0, 256, 0);

		for($i = 0; $i < 8; $i++){
			$this->expTable[$i] = 1 << $i;
		}

		for($i = 8; $i < 256; $i++){
			$this->expTable[$i] = $this->expTable[$i - 4]^$this->expTable[$i - 5]^$this->expTable[$i - 6]^$this->expTable[$i - 8];
		}

		for($i = 0; $i < 255; $i++){
			$this->logTable[$this->expTable[$i]] = $i;
		}

	}

	/**
	 * @param array $e
	 *
	 * @return void
	 */
	public function multiply(array $e){
		$n = array_fill(0, count($this->num) + count($e) - 1, 0);

		foreach($this->num as $i => &$vi){
			foreach($e as $j => &$vj){
				$n[$i + $j] ^= $this->gexp($this->glog($vi) + $this->glog($vj));
			}
		}

		$this->setNum($n);
	}

	/**
	 * @param array $e
	 *
	 * @return void
	 */
	public function mod(array $e){
		$n = $this->num;

		if(count($n) - count($e) < 0){
			return;
		}

		$ratio = $this->glog($n[0]) - $this->glog($e[0]);

		foreach($e as $i => &$v){
			$n[$i] ^= $this->gexp($this->glog($v) + $ratio);
		}

		$this->setNum($n)->mod($e);
	}

	/**
	 * @param int $n
	 *
	 * @return int
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	public function glog(int $n):int {

		if($n < 1){
			throw new QRCodeException('log('.$n.')');
		}

		return $this->logTable[$n];
	}

	/**
	 * @param int $n
	 *
	 * @return int
	 */
	public function gexp(int $n):int {

		if($n < 0){
			$n += 255;
		}
		elseif($n >= 256){
			$n -= 255;
		}

		return $this->expTable[$n];
	}

}
