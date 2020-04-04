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
 *
 * @noinspection PhpUnused
 */

namespace chillerlan\QRCode\Data;

use function abs, array_search, call_user_func_array, min;

/**
 * Receives a QRDataInterface object and runs the mask pattern tests on it.
 *
 * ISO/IEC 18004:2000 Section 8.8.2 - Evaluation of masking results
 *
 * @see http://www.thonky.com/qr-code-tutorial/data-masking
 */
final class MaskPatternTester{

	/**
	 * The data interface that contains the data matrix to test
	 */
	protected QRDataInterface $dataInterface;

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
		$matrix  = $this->dataInterface->initMatrix($pattern, true);
		$penalty = 0;

		for($level = 1; $level <= 4; $level++){
			$penalty += call_user_func_array([$this, 'testLevel'.$level], [$matrix, $matrix->size()]);
		}

		return (int)$penalty;
	}

	/**
	 * Checks for each group of five or more same-colored modules in a row (or column)
	 */
	protected function testLevel1(QRMatrix $m, int $size):float{
		$penalty = 0;

		foreach($m->matrix() as $y => $row){
			foreach($row as $x => $val){
				$count = 0;

				for($ry = -1; $ry <= 1; $ry++){

					if($y + $ry < 0 || $size <= $y + $ry){
						continue;
					}

					for($rx = -1; $rx <= 1; $rx++){

						if(($ry === 0 && $rx === 0) || (($x + $rx) < 0 || $size <= ($x + $rx))){
							continue;
						}

						if($m->check($x + $rx, $y + $ry) === (($val >> 8) > 0)){
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
	protected function testLevel2(QRMatrix $m, int $size):float{
		$penalty = 0;

		foreach($m->matrix() as $y => $row){

			if($y > $size - 2){
				break;
			}

			foreach($row as $x => $val){

				if($x > $size - 2){
					break;
				}

				$count = 0;

				if($val >> 8 > 0){
					$count++;
				}

				if($m->check($y, $x + 1)){
					$count++;
				}

				if($m->check($y + 1, $x)){
					$count++;
				}

				if($m->check($y + 1, $x + 1)){
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
	 * Checks if there are patterns that look similar to the finder patterns (1:1:3:1:1 ratio)
	 */
	protected function testLevel3(QRMatrix $m, int $size):float{
		$penalty = 0;

		foreach($m->matrix() as $y => $row){
			foreach($row as $x => $val){

				if($x <= $size - 7){
					if(
						    $m->check($x    , $y)
						&& !$m->check($x + 1, $y)
						&&  $m->check($x + 2, $y)
						&&  $m->check($x + 3, $y)
						&&  $m->check($x + 4, $y)
						&& !$m->check($x + 5, $y)
						&&  $m->check($x + 6, $y)
					){
						$penalty += 40;
					}
				}

				if($y <= $size - 7){
					if(
						    $m->check($x, $y)
						&& !$m->check($x, $y + 1)
						&&  $m->check($x, $y + 2)
						&&  $m->check($x, $y + 3)
						&&  $m->check($x, $y + 4)
						&& !$m->check($x, $y + 5)
						&&  $m->check($x, $y + 6)
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
	protected function testLevel4(QRMatrix $m, int $size):float{
		$count = 0;

		foreach($m->matrix() as $y => $row){
			foreach($row as $x => $val){
				if(($val >> 8) > 0){
					$count++;
				}
			}
		}

		return (abs(100 * $count / $size / $size - 50) / 5) * 10;
	}

}
