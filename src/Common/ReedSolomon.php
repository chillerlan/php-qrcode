<?php
/**
 * Class ReedSolomon
 *
 * @filesource   ReedSolomon.php
 * @created      07.01.2021
 * @package      chillerlan\QRCode\Common
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Common;

use chillerlan\QRCode\Helpers\BitBuffer;
use chillerlan\QRCode\Helpers\Polynomial;

use SplFixedArray;
use function array_fill, array_merge, count, max;

/**
 * ISO/IEC 18004:2000 Section 8.5 ff
 *
 * @see http://www.thonky.com/qr-code-tutorial/error-correction-coding
 */
final class ReedSolomon{

	private Version       $version;
	private EccLevel      $eccLevel;

	private SplFixedArray $interleavedData;
	private int           $interleavedDataIndex;

	/**
	 * ReedSolomon constructor.
	 */
	public function __construct(Version $version, EccLevel $eccLevel){
		$this->version  = $version;
		$this->eccLevel = $eccLevel;
	}

	/**
	 * ECC interleaving
	 *
	 * @return \SplFixedArray<int>
	 */
	public function interleaveEcBytes(BitBuffer $bitBuffer):SplFixedArray{
		[$numEccCodewords, [[$l1, $b1], [$l2, $b2]]] = $this->version->getRSBlocks($this->eccLevel);

		$numRsBlocks = $l1 + $l2;
		$ecBytes     = new SplFixedArray($numRsBlocks);
		$rsBlocks    = array_fill(0, $l1, [$numEccCodewords + $b1, $b1]);

		if($l2 > 0){
			$rsBlocks = array_merge($rsBlocks, array_fill(0, $l2, [$numEccCodewords + $b2, $b2]));
		}

		$dataBytes      = SplFixedArray::fromArray($rsBlocks);
		$maxDataBytes   = 0;
		$maxEcBytes     = 0;
		$dataByteOffset = 0;
		$bitBufferData  = $bitBuffer->getBuffer();

		foreach($rsBlocks as $key => $block){
			[$rsBlockTotal, $dataByteCount] = $block;

			$ecByteCount     = $rsBlockTotal - $dataByteCount;
			$maxDataBytes    = max($maxDataBytes, $dataByteCount);
			$maxEcBytes      = max($maxEcBytes, $ecByteCount);
			$dataBytes[$key] = new SplFixedArray($dataByteCount);

			foreach($dataBytes[$key] as $i => $_){
				$dataBytes[$key][$i] = $bitBufferData[$i + $dataByteOffset] & 0xff;
			}

			$rsPoly  = new Polynomial;
			$modPoly = new Polynomial;

			for($i = 0; $i < $ecByteCount; $i++){
				$modPoly->setNum([1, $modPoly->gexp($i)]);
				$rsPoly->multiply($modPoly->getNum());
			}

			$rsPolyCount = count($rsPoly->getNum()) - 1;

			$modPoly
				->setNum($dataBytes[$key]->toArray(), $rsPolyCount)
				->mod($rsPoly->getNum())
			;

			$ecBytes[$key] = new SplFixedArray($rsPolyCount);
			$num           = $modPoly->getNum();
			$count         = count($num) - count($ecBytes[$key]);

			foreach($ecBytes[$key] as $i => $_){
				$modIndex          = $i + $count;
				$ecBytes[$key][$i] = $modIndex >= 0 ? $num[$modIndex] : 0;
			}

			$dataByteOffset += $dataByteCount;
		}

		$this->interleavedData      = new SplFixedArray($this->version->getTotalCodewords());
		$this->interleavedDataIndex = 0;

		$this->interleave($dataBytes, $maxDataBytes, $numRsBlocks);
		$this->interleave($ecBytes, $maxEcBytes, $numRsBlocks);

		return $this->interleavedData;
	}

	/**
	 *
	 */
	private function interleave(SplFixedArray $byteArray, int $maxBytes, int $numRsBlocks):void{
		for($x = 0; $x < $maxBytes; $x++){
			for($y = 0; $y < $numRsBlocks; $y++){
				if($x < count($byteArray[$y])){
					$this->interleavedData[$this->interleavedDataIndex++] = $byteArray[$y][$x];
				}
			}
		}
	}

}
