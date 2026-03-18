<?php
/**
 * Class ResultPoint
 *
 * @created      17.01.2021
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Detector;

use function abs;

/**
 * Encapsulates a point of interest in an image containing a barcode. Typically, this
 * would be the location of a finder pattern or the corner of the barcode, for example.
 *
 * @author Sean Owen
 */
abstract class ResultPoint{

	protected(set) float $x;
	protected(set) float $y;
	protected(set) float $estimatedModuleSize;

	public function __construct(float $x, float $y, float $estimatedModuleSize){
		$this->x                   = $x;
		$this->y                   = $y;
		$this->estimatedModuleSize = $estimatedModuleSize;
	}

	/**
	 * Determines if this finder pattern "about equals" a finder pattern at the stated
	 * position and size -- meaning, it is at nearly the same center with nearly the same size.
	 */
	public function aboutEquals(float $moduleSize, float $i, float $j):bool{

		if(abs($i - $this->y) <= $moduleSize && abs($j - $this->x) <= $moduleSize){
			$moduleSizeDiff = abs($moduleSize - $this->estimatedModuleSize);

			return $moduleSizeDiff <= 1.0 || $moduleSizeDiff <= $this->estimatedModuleSize;
		}

		return false;
	}

}
