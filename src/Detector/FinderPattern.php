<?php
/**
 * Class FinderPattern
 *
 * @created      17.01.2021
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 */

namespace chillerlan\QRCode\Detector;

use function chillerlan\QRCode\Common\{distance, squaredDistance};

/**
 * <p>Encapsulates a finder pattern, which are the three square patterns found in
 * the corners of QR Codes. It also encapsulates a count of similar finder patterns,
 * as a convenience to the finder's bookkeeping.</p>
 *
 * @author Sean Owen
 */
final class FinderPattern extends ResultPoint{

	private int $count;

	public function __construct(float $posX, float $posY, float $estimatedModuleSize, int $count = 1){
		parent::__construct($posX, $posY, $estimatedModuleSize);

		$this->count = $count;
	}

	public function getCount():int{
		return $this->count;
	}

	/**
	 * @param \chillerlan\QRCode\Detector\FinderPattern $b second pattern
	 *
	 * @return float distance between two points
	 */
	public function distance(FinderPattern $b):float{
		return distance($this->getX(), $this->getY(), $b->getX(), $b->getY());
	}

	/**
	 * Get square of distance between a and b.
	 */
	public function squaredDistance(FinderPattern $b):float{
		return squaredDistance($this->getX(), $this->getY(), $b->getX(), $b->getY());
	}

	/**
	 * Combines this object's current estimate of a finder pattern position and module size
	 * with a new estimate. It returns a new {@code FinderPattern} containing a weighted average
	 * based on count.
	 */
	public function combineEstimate(float $i, float $j, float $newModuleSize):FinderPattern{
		$combinedCount = $this->count + 1;

		return new self(
			($this->count * $this->x + $j) / $combinedCount,
			($this->count * $this->y + $i) / $combinedCount,
			($this->count * $this->estimatedModuleSize + $newModuleSize) / $combinedCount,
			$combinedCount
		);
	}

}
