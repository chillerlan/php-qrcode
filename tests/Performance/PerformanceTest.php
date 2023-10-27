<?php
/**
 * Class PerformanceTest
 *
 * @created      16.10.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Performance;

use Closure;
use function hrtime;

/**
 *
 */
class PerformanceTest{

	protected int $runs;
	protected int $total = 0;

	public function __construct(int $runs = 1000){
		$this->runs = $runs;
	}

	public function run(Closure $subject):self{
		$this->total = 0;

		for($i = 0; $i < $this->runs; $i++){
			$start = hrtime(true);

			$subject();

			$end = hrtime(true);

			$this->total += ($end - $start);
		}

		return $this;
	}

	public function getResult():float{
		return ($this->total / $this->runs / 1000000);
	}

}
