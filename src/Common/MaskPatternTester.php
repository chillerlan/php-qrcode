<?php
/**
 * Class MaskPatternTester
 *
 * @created      22.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */

namespace chillerlan\QRCode\Common;

use chillerlan\QRCode\Data\QRData;
use function abs, array_search, call_user_func_array, min;

/**
 * Receives a QRData object and runs the mask pattern tests on it.
 *
 * ISO/IEC 18004:2000 Section 8.8.2 - Evaluation of masking results
 *
 * @see http://www.thonky.com/qr-code-tutorial/data-masking
 */
final class MaskPatternTester{

	/**
	 * The data interface that contains the data matrix to test
	 */
	private QRData $qrData;

	/**
	 * Receives the QRData object
	 *
	 * @see \chillerlan\QRCode\QROptions::$maskPattern
	 * @see \chillerlan\QRCode\Data\QRMatrix::$maskPattern
	 */
	public function __construct(QRData $qrData){
		$this->qrData = $qrData;
	}

	/**
	 * shoves a QRMatrix through the MaskPatternTester to find the lowest penalty mask pattern
	 *
	 * @see \chillerlan\QRCode\Data\MaskPatternTester
	 */
	public function getBestMaskPattern():MaskPattern{
		$penalties = [];

		foreach(MaskPattern::PATTERNS as $pattern){
			$penalties[$pattern] = $this->testPattern(new MaskPattern($pattern));
		}

		return new MaskPattern(array_search(min($penalties), $penalties, true));
	}

	/**
	 * Returns the penalty for the given mask pattern
	 *
	 * @see \chillerlan\QRCode\QROptions::$maskPattern
	 * @see \chillerlan\QRCode\Data\QRMatrix::$maskPattern
	 */
	public function testPattern(MaskPattern $pattern):int{
		$matrix  = $this->qrData->writeMatrix($pattern, true);
		$penalty = 0;

		for($level = 1; $level <= 4; $level++){
			$penalty += call_user_func_array([$this, 'testLevel'.$level], [$matrix->matrix(true), $matrix->size()]);
		}

		return (int)$penalty;
	}

	/**
	 * Apply mask penalty rule 1 and return the penalty. Find repetitive cells with the same color and
	 * give penalty to them. Example: 00000 or 11111.
	 */
	private function testLevel1(array $m, int $size):int{
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
	 * Apply mask penalty rule 2 and return the penalty. Find 2x2 blocks with the same color and give
	 * penalty to them. This is actually equivalent to the spec's rule, which is to find MxN blocks and give a
	 * penalty proportional to (M-1)x(N-1), because this is the number of 2x2 blocks inside such a block.
	 */
	private function testLevel2(array $m, int $size):int{
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
	 * Apply mask penalty rule 3 and return the penalty. Find consecutive runs of 1:1:3:1:1:4
	 * starting with black, or 4:1:1:3:1:1 starting with white, and give penalty to them.  If we
	 * find patterns like 000010111010000, we give penalty once.
	 */
	private function testLevel3(array $m, int $size):int{
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
	 * Apply mask penalty rule 4 and return the penalty. Calculate the ratio of dark cells and give
	 * penalty if the ratio is far from 50%. It gives 10 penalty for 5% distance.
	 */
	private function testLevel4(array $m, int $size):float{
		$count = 0;

		foreach($m as $y => $row){
			foreach($row as $x => $val){
				if($val){
					$count++;
				}
			}
		}

		return (abs(100 * $count / $size / $size - 50) / 5) * 10;
	}

}
