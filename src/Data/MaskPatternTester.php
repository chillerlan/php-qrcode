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

use function abs, array_search, call_user_func, min;

/**
 * Receives a QRDataInterface object and runs the mask pattern tests on it.
 *
 * @link http://www.thonky.com/qr-code-tutorial/data-masking
 */
final class MaskPatternTester{

	protected QRMatrix $matrix;

	protected QRDataInterface $dataInterface;

	protected int $moduleCount;

	/**
	 * Receives the QRDataInterface
	 *
	 * @see \chillerlan\QRCode\QROptions::$maskPattern
	 * @see \chillerlan\QRCode\Data\QRMatrix::$maskPattern
	 */
	public function __construct(QRDataInterface $dataInterface){
		$this->dataInterface = $dataInterface;
	}

	/**
	 * shoves a QRMatrix through the MaskPatternTester to find the lowest penalty mask pattern
	 *
	 * @see \chillerlan\QRCode\Data\MaskPatternTester
	 */
	public function getBestMaskPattern():int{
		$penalties = [];

		for($pattern = 0; $pattern < 8; $pattern++){
			$penalties[$pattern] = $this->testPattern($pattern);
		}

		return array_search(min($penalties), $penalties, true);
	}

	/**
	 * Returns the penalty for the given mask pattern
	 *
	 * @see \chillerlan\QRCode\QROptions::$maskPattern
	 * @see \chillerlan\QRCode\Data\QRMatrix::$maskPattern
	 */
	public function testPattern(int $pattern):int{
		$this->matrix      = $this->dataInterface->initMatrix($pattern, true);
		$this->moduleCount = $this->matrix->size();
		$penalty           = 0;

		for($level = 1; $level <= 4; $level++){
			$penalty += call_user_func([$this, 'testLevel'.$level]);
		}

		return (int)$penalty;
	}

	/**
	 * Checks for each group of five or more same-colored modules in a row (or column)
	 */
	protected function testLevel1():float{
		$penalty = 0;

		foreach($this->matrix->matrix() as $y => $row){
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

						if($this->matrix->check($x + $rx, $y + $ry) === (($val >> 8) > 0)){
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
	 */
	protected function testLevel2():float{
		$penalty = 0;

		foreach($this->matrix->matrix() as $y => $row){

			if($y > $this->moduleCount - 2){
				break;
			}

			foreach($row as $x => $val){

				if($x > $this->moduleCount - 2){
					break;
				}

				$count = 0;

				if($val >> 8 > 0){
					$count++;
				}

				if($this->matrix->check($y, $x + 1)){
					$count++;
				}

				if($this->matrix->check($y + 1, $x)){
					$count++;
				}

				if($this->matrix->check($y + 1, $x + 1)){
					$count++;
				}

				if($count === 0 || $count === 4){
					$penalty += 3;
				}

			}
		}

		return $penalty;
	}

	/**
	 * Checks if there are patterns that look similar to the finder patterns
	 */
	protected function testLevel3():float{
		$penalty = 0;

		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $val){

				if($x <= $this->moduleCount - 7){
					if(
						    $this->matrix->check($x    , $y)
						&& !$this->matrix->check($x + 1, $y)
						&&  $this->matrix->check($x + 2, $y)
						&&  $this->matrix->check($x + 3, $y)
						&&  $this->matrix->check($x + 4, $y)
						&& !$this->matrix->check($x + 5, $y)
						&&  $this->matrix->check($x + 6, $y)
					){
						$penalty += 40;
					}
				}

				if($y <= $this->moduleCount - 7){
					if(
						    $this->matrix->check($x, $y)
						&& !$this->matrix->check($x, $y + 1)
						&&  $this->matrix->check($x, $y + 2)
						&&  $this->matrix->check($x, $y + 3)
						&&  $this->matrix->check($x, $y + 4)
						&& !$this->matrix->check($x, $y + 5)
						&&  $this->matrix->check($x, $y + 6)
					){
						$penalty += 40;
					}
				}

			}
		}

		return $penalty;
	}

	/**
	 * Checks if more than half of the modules are dark or light, with a larger penalty for a larger difference
	 */
	protected function testLevel4():float{
		$count = 0;

		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $val){
				if(($val >> 8) > 0){
					$count++;
				}
			}
		}

		return (abs(100 * $count / $this->moduleCount / $this->moduleCount - 50) / 5) * 10;
	}

}
