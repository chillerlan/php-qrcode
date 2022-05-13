<?php
/**
 * Class ReedSolomonEncoder
 *
 * @created      07.01.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Common;

use chillerlan\QRCode\Data\QRMatrix;
use function array_fill, array_merge, count, max;

/**
 * ISO/IEC 18004:2000 Section 8.5 ff
 *
 * @see http://www.thonky.com/qr-code-tutorial/error-correction-coding
 */
final class ReedSolomonEncoder{

	private array $interleavedData;
	private int   $interleavedDataIndex;

	/**
	 * ECC interleaving
	 */
	public function interleaveEcBytes(BitBuffer $bitBuffer, QRMatrix $matrix):QRMatrix{
		$version = $matrix->version();
		[$numEccCodewords, [[$l1, $b1], [$l2, $b2]]] = $version->getRSBlocks($matrix->eccLevel());

		$rsBlocks = array_fill(0, $l1, [$numEccCodewords + $b1, $b1]);

		if($l2 > 0){
			$rsBlocks = array_merge($rsBlocks, array_fill(0, $l2, [$numEccCodewords + $b2, $b2]));
		}

		$bitBufferData  = $bitBuffer->getBuffer();
		$dataBytes      = [];
		$ecBytes        = [];
		$maxDataBytes   = 0;
		$maxEcBytes     = 0;
		$dataByteOffset = 0;

		foreach($rsBlocks as $key => $block){
			[$rsBlockTotal, $dataByteCount] = $block;

			$dataBytes[$key] = [];

			for($i = 0; $i < $dataByteCount; $i++){
				$dataBytes[$key][$i] = $bitBufferData[$i + $dataByteOffset] & 0xff;
			}

			$ecByteCount    = $rsBlockTotal - $dataByteCount;
			$ecBytes[$key]  = $this->generateEcBytes($dataBytes[$key], $ecByteCount);
			$maxDataBytes   = max($maxDataBytes, $dataByteCount);
			$maxEcBytes     = max($maxEcBytes, $ecByteCount);
			$dataByteOffset += $dataByteCount;
		}

		$this->interleavedData      = array_fill(0, $version->getTotalCodewords(), 0);
		$this->interleavedDataIndex = 0;
		$numRsBlocks                = $l1 + $l2;

		$this->interleave($dataBytes, $maxDataBytes, $numRsBlocks);
		$this->interleave($ecBytes, $maxEcBytes, $numRsBlocks);

		return $this->mapData($matrix);
	}

	/**
	 *
	 */
	private function generateEcBytes(array $dataBytes, int $ecByteCount):array{
		$rsPoly = new GenericGFPoly([1]);

		for($i = 0; $i < $ecByteCount; $i++){
			$rsPoly = $rsPoly->multiply(new GenericGFPoly([1, GF256::exp($i)]));
		}

		$rsPolyDegree = $rsPoly->getDegree();

		$modCoefficients = (new GenericGFPoly($dataBytes, $rsPolyDegree))
			->mod($rsPoly)
			->getCoefficients()
		;

		$ecBytes = array_fill(0, $rsPolyDegree, 0);
		$count   = count($modCoefficients) - $rsPolyDegree;

		foreach($ecBytes as $i => &$val){
			$modIndex = $i + $count;
			$val      = $modIndex >= 0 ? $modCoefficients[$modIndex] : 0;
		}

		return $ecBytes;
	}

	/**
	 *
	 */
	private function interleave(array $byteArray, int $maxBytes, int $numRsBlocks):void{
		for($x = 0; $x < $maxBytes; $x++){
			for($y = 0; $y < $numRsBlocks; $y++){
				if($x < count($byteArray[$y])){
					$this->interleavedData[$this->interleavedDataIndex++] = $byteArray[$y][$x];
				}
			}
		}
	}

	/**
	 * Maps the interleaved binary $data on the matrix
	 */
	private function mapData(QRMatrix $matrix):QRMatrix{
		$byteCount = count($this->interleavedData);
		$size      = $matrix->size();
		$y         = $size - 1;
		$inc       = -1;
		$byteIndex = 0;
		$bitIndex  = 7;

		for($i = $y; $i > 0; $i -= 2){

			if($i === 6){
				$i--;
			}

			while(true){
				for($c = 0; $c < 2; $c++){
					$x = $i - $c;

					if($matrix->get($x, $y) !== QRMatrix::M_NULL){
						continue;
					}

					$v = false;

					if($byteIndex < $byteCount){
						$v = (($this->interleavedData[$byteIndex] >> $bitIndex) & 1) === 1;
					}

					$matrix->set($x, $y, $v, QRMatrix::M_DATA);
					$bitIndex--;

					if($bitIndex === -1){
						$byteIndex++;
						$bitIndex = 7;
					}

				}

				$y += $inc;

				if($y < 0 || $size <= $y){
					$y   -=  $inc;
					$inc  = -$inc;

					break;
				}

			}
		}

		return $matrix;
	}

}
