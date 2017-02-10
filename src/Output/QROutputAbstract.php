<?php
/**
 * Class QROutputAbstract
 *
 * @filesource   QROutputAbstract.php
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
abstract class QROutputAbstract implements QROutputInterface{

	/**
	 * @var string
	 */
	protected $optionsInterface;

	/**
	 * @var array
	 */
	protected $types;

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
	 * @var \chillerlan\QRCode\Output\QROutputOptionsAbstract $outputOptions
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function __construct(QROutputOptionsAbstract $outputOptions = null){
		$this->options = $outputOptions;

		if($this->optionsInterface && !$this->options instanceof $this->optionsInterface){
			$this->options = new $this->optionsInterface;
		}

		if(is_array($this->types) && !in_array($this->options->type, $this->types , true)){
			throw new QRCodeOutputException('Invalid output type!');
		}

	}

	/**
	 * @param array $matrix
	 *
	 * @return \chillerlan\QRCode\Output\QROutputInterface
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function setMatrix(array $matrix):QROutputInterface {
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
