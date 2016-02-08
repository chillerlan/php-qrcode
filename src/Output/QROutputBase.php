<?php
/**
 * Class QROutputBase
 *
 * @filesource   QROutputBase.php
 * @created      09.12.2015
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

/**
 *
 */
class QROutputBase{

	/**
	 * @var array
	 */
	protected $matrix;

	/**
	 * @var int
	 */
	protected $pixelCount;

	/**
	 * @var object
	 */
	protected $options;

	/**
	 * @param array $matrix
	 *
	 * @return $this
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function setMatrix(array $matrix){
		$this->pixelCount = count($matrix);

		// specify valid range?
		if($this->pixelCount < 2
			|| !isset($matrix[$this->pixelCount - 1])
			|| $this->pixelCount !== count($matrix[$this->pixelCount - 1])
		){
			throw new QRCodeOutputException('Invalid matrix!');
		}

		$this->matrix = $matrix;

		return $this;
	}

}
