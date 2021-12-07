<?php
/**
 * Class ReedSolomonDecoder
 *
 * @created      24.01.2021
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 */

namespace chillerlan\QRCode\Common;

use RuntimeException;
use function array_fill, count;

/**
 * Implements Reed-Solomon decoding, as the name implies.
 *
 * The algorithm will not be explained here, but the following references were helpful
 * in creating this implementation:
 *
 * - Bruce Maggs "Decoding Reed-Solomon Codes" (see discussion of Forney's Formula)
 *   http://www.cs.cmu.edu/afs/cs.cmu.edu/project/pscico-guyb/realworld/www/rs_decode.ps
 * - J.I. Hall. "Chapter 5. Generalized Reed-Solomon Codes" (see discussion of Euclidean algorithm)
 *   https://users.math.msu.edu/users/halljo/classes/codenotes/GRS.pdf
 *
 * Much credit is due to William Rucklidge since portions of this code are an indirect
 * port of his C++ Reed-Solomon implementation.
 *
 * @author Sean Owen
 * @author William Rucklidge
 * @author sanfordsquires
 */
final class ReedSolomonDecoder{

	/**
	 * Decodes given set of received codewords, which include both data and error-correction
	 * codewords. Really, this means it uses Reed-Solomon to detect and correct errors, in-place,
	 * in the input.
	 *
	 * @param array $received        data and error-correction codewords
	 * @param int   $numEccCodewords number of error-correction codewords available
	 *
	 * @return int[]
	 * @throws \RuntimeException if decoding fails for any reason
	 */
	public function decode(array $received, int $numEccCodewords):array{
		$poly                 = new GenericGFPoly($received);
		$syndromeCoefficients = [];
		$noError              = true;

		for($i = 0, $j = $numEccCodewords - 1; $i < $numEccCodewords; $i++, $j--){
			$eval                     = $poly->evaluateAt(GF256::exp($i));
			$syndromeCoefficients[$j] = $eval;

			if($eval !== 0){
				$noError = false;
			}
		}

		if($noError){
			return $received;
		}

		[$sigma, $omega] = $this->runEuclideanAlgorithm(
			GF256::buildMonomial($numEccCodewords, 1),
			new GenericGFPoly($syndromeCoefficients),
			$numEccCodewords
		);

		$errorLocations      = $this->findErrorLocations($sigma);
		$errorMagnitudes     = $this->findErrorMagnitudes($omega, $errorLocations);
		$errorLocationsCount = count($errorLocations);
		$receivedCount       = count($received);

		for($i = 0; $i < $errorLocationsCount; $i++){
			$position = $receivedCount - 1 - GF256::log($errorLocations[$i]);

			if($position < 0){
				throw new RuntimeException('Bad error location');
			}

			$received[$position] ^= $errorMagnitudes[$i];
		}

		return $received;
	}

	/**
	 * @return \chillerlan\QRCode\Common\GenericGFPoly[] [sigma, omega]
	 * @throws \RuntimeException
	 */
	private function runEuclideanAlgorithm(GenericGFPoly $a, GenericGFPoly $b, int $R):array{
		// Assume a's degree is >= b's
		if($a->getDegree() < $b->getDegree()){
			$temp = $a;
			$a    = $b;
			$b    = $temp;
		}

		$rLast = $a;
		$r     = $b;
		$tLast = new GenericGFPoly([0]);
		$t     = new GenericGFPoly([1]);

		// Run Euclidean algorithm until r's degree is less than R/2
		while(2 * $r->getDegree() >= $R){
			$rLastLast = $rLast;
			$tLastLast = $tLast;
			$rLast     = $r;
			$tLast     = $t;

			// Divide rLastLast by rLast, with quotient in q and remainder in r
			[$q, $r] = $rLastLast->divide($rLast);

			$t = $q->multiply($tLast)->addOrSubtract($tLastLast);

			if($r->getDegree() >= $rLast->getDegree()){
				throw new RuntimeException('Division algorithm failed to reduce polynomial?');
			}
		}

		$sigmaTildeAtZero = $t->getCoefficient(0);

		if($sigmaTildeAtZero === 0){
			throw new RuntimeException('sigmaTilde(0) was zero');
		}

		$inverse = GF256::inverse($sigmaTildeAtZero);

		return [$t->multiplyInt($inverse), $r->multiplyInt($inverse)];
	}

	/**
	 * @throws \RuntimeException
	 */
	private function findErrorLocations(GenericGFPoly $errorLocator):array{
		// This is a direct application of Chien's search
		$numErrors = $errorLocator->getDegree();

		if($numErrors === 1){ // shortcut
			return [$errorLocator->getCoefficient(1)];
		}

		$result = array_fill(0, $numErrors, 0);
		$e      = 0;

		for($i = 1; $i < 256 && $e < $numErrors; $i++){
			if($errorLocator->evaluateAt($i) === 0){
				$result[$e] = GF256::inverse($i);
				$e++;
			}
		}

		if($e !== $numErrors){
			throw new RuntimeException('Error locator degree does not match number of roots');
		}

		return $result;
	}

	/**
	 *
	 */
	private function findErrorMagnitudes(GenericGFPoly $errorEvaluator, array $errorLocations):array{
		// This is directly applying Forney's Formula
		$s      = count($errorLocations);
		$result = [];

		for($i = 0; $i < $s; $i++){
			$xiInverse   = GF256::inverse($errorLocations[$i]);
			$denominator = 1;

			for($j = 0; $j < $s; $j++){
				if($i !== $j){
#					$denominator = GF256::multiply($denominator, GF256::addOrSubtract(1, GF256::multiply($errorLocations[$j], $xiInverse)));
					// Above should work but fails on some Apple and Linux JDKs due to a Hotspot bug.
					// Below is a funny-looking workaround from Steven Parkes
					$term        = GF256::multiply($errorLocations[$j], $xiInverse);
					$denominator = GF256::multiply($denominator, (($term & 0x1) === 0 ? $term | 1 : $term & ~1));
				}
			}

			$result[$i] = GF256::multiply($errorEvaluator->evaluateAt($xiInverse), GF256::inverse($denominator));
		}

		return $result;
	}

}
