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
			$penalty += call_user_func_array([$this, 'testLevel'.$level], [$matrix->matrix(true), $matrix->size()]);
		}

		return (int)$penalty;
	}

	/**
	 * Checks for each group of five or more same-colored modules in a row (or column)
	 */
	protected function testLevel1(array $m, int $size):int{
		$penalty = 0;

		foreach($m as $y => $row){
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
	 */
	protected function testLevel2(array $m, int $size):int{
		$penalty = 0;

		foreach($m as $y => $row){

			if($y > $size - 2){
				break;
			}

			foreach($row as $x => $val){

				if($x > $size - 2){
					break;
				}

				if(
					   $val === $row[$x + 1]
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
	 */
	protected function testLevel3(array $m, int $size):int{
		$penalties = 0;

		foreach($m as $y => $row){
			foreach($row as $x => $val){

				if(
					$x + 6 < $size
					&&  $val
					&& !$row[$x + 1]
					&&  $row[$x + 2]
					&&  $row[$x + 3]
					&&  $row[$x + 4]
					&& !$row[$x + 5]
					&&  $row[$x + 6]
				){
					$penalties++;
				}

				if(
					$y + 6 < $size
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
	 */
	protected function testLevel4(array $m, int $size):float{
		$count = 0;

		foreach($m as $row){
			foreach($row as $val){
				if($val){
					$count++;
				}
			}
		}

		return (abs(100 * $count / $size / $size - 50) / 5) * 10;
	}

}
