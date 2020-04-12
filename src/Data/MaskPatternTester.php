<?php
/**
 * Class MaskPatternTester
 *
 * @filesource   MaskPatternTester.php
 * @created      22.11.2017
 * @package      chillerlan\QRCode\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use function abs, call_user_func_array;

/**
 * The sole purpose of this class is to receive a QRMatrix object and run the pattern tests on it.
 *
 * @link http://www.thonky.com/qr-code-tutorial/data-masking
 */
class MaskPatternTester{

	/**
	 * @var \chillerlan\QRCode\Data\QRMatrix
	 */
	protected $matrix;

	/**
	 * @var int
	 */
	protected $moduleCount;

	/**
	 * Receives the matrix an sets the module count
	 *
	 * @see \chillerlan\QRCode\QROptions::$maskPattern
	 * @see \chillerlan\QRCode\Data\QRMatrix::$maskPattern
	 * @see \chillerlan\QRCode\QRCode::getBestMaskPattern()
	 *
	 * @param \chillerlan\QRCode\Data\QRMatrix $matrix
	 */
	public function __construct(QRMatrix $matrix){
		$this->matrix      = $matrix;
		$this->moduleCount = $this->matrix->size();
	}

	/**
	 * Returns the penalty for the given mask pattern
	 *
	 * @see \chillerlan\QRCode\QROptions::$maskPattern
	 * @see \chillerlan\QRCode\Data\QRMatrix::$maskPattern
	 * @see \chillerlan\QRCode\QRCode::getBestMaskPattern()
	 *
	 * @return int
	 */
	public function testPattern():int{
		$penalty  = 0;

		for($level = 1; $level <= 4; $level++){
			$penalty += call_user_func_array([$this, 'testLevel'.$level], [$this->matrix->matrix(true)]);
		}

		return (int)$penalty;
	}

	/**
	 * Checks for each group of five or more same-colored modules in a row (or column)
	 *
	 * @return int
	 */
	protected function testLevel1(array $m):int{
		$penalty = 0;

		foreach($m as $y => $row){
			foreach($row as $x => $val){
				$count = 0;

				for($ry = -1; $ry <= 1; $ry++){

					if($y + $ry < 0 || $this->moduleCount <= $y + $ry){
						continue;
					}

					for($rx = -1; $rx <= 1; $rx++){

						if(($ry === 0 && $rx === 0) || (($x + $rx) < 0 || $this->moduleCount <= ($x + $rx))){
							continue;
						}

						if($m[$y + $ry][$x + $rx] === $val){
							$count++;
						}

					}
				}

				if($count > 5){
					$penalty += (3 + $count - 5);
				}

			}
		}

		return $penalty;
	}

	/**
	 * Checks for each 2x2 area of same-colored modules in the matrix
	 *
	 * @return int
	 */
	protected function testLevel2(array $m):int{
		$penalty = 0;

		foreach($m as $y => $row){

			if($y > ($this->moduleCount - 2)){
				break;
			}

			foreach($row as $x => $val){

				if($x > ($this->moduleCount - 2)){
					break;
				}

				if(
					   $val === $m[$y][$x + 1]
					&& $val === $m[$y + 1][$x]
					&& $val === $m[$y + 1][$x + 1]
				){
					$penalty++;
				}
			}
		}

		return 3 * $penalty;
	}

	/**
	 * Checks if there are patterns that look similar to the finder patterns (1:1:3:1:1 ratio)
	 *
	 * @return int
	 */
	protected function testLevel3(array $m):int{
		$penalties = 0;

		foreach($m as $y => $row){
			foreach($row as $x => $val){

				if(
					($x + 6) < $this->moduleCount
					&&  $val
					&& !$m[$y][$x + 1]
					&&  $m[$y][$x + 2]
					&&  $m[$y][$x + 3]
					&&  $m[$y][$x + 4]
					&& !$m[$y][$x + 5]
					&&  $m[$y][$x + 6]
				){
					$penalties++;
				}

				if(
					($y + 6) < $this->moduleCount
					&&  $val
					&& !$m[$y + 1][$x]
					&&  $m[$y + 2][$x]
					&&  $m[$y + 3][$x]
					&&  $m[$y + 4][$x]
					&& !$m[$y + 5][$x]
					&&  $m[$y + 6][$x]
				){
					$penalties++;
				}

			}
		}

		return $penalties * 40;
	}

	/**
	 * Checks if more than half of the modules are dark or light, with a larger penalty for a larger difference
	 *
	 * @return float
	 */
	protected function testLevel4(array $m):float{
		$count = 0;

		foreach($m as $y => $row){
			foreach($row as $x => $val){
				if($val){
					$count++;
				}
			}
		}

		return (abs(100 * $count / $this->moduleCount / $this->moduleCount - 50) / 5) * 10;
	}

}
