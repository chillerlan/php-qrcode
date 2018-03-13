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

use chillerlan\QRCode\{
	Data\QRMatrix, QRCode
};
use chillerlan\Traits\ContainerInterface;

/**
 *
 */
abstract class QROutputAbstract implements QROutputInterface{

	/**
	 * @var int
	 */
	protected $moduleCount;

	/**
	 * @param \chillerlan\QRCode\Data\QRMatrix $matrix
	 */
	protected $matrix;

	/**
	 * @var \chillerlan\QRCode\QROptions
	 */
	protected $options;

	/**
	 * @var string
	 */
	protected $outputMode;

	/**
	 * @var string;
	 */
	protected $defaultMode;

	/**
	 * QROutputAbstract constructor.
	 *
	 * @param \chillerlan\Traits\ContainerInterface $options
	 * @param \chillerlan\QRCode\Data\QRMatrix      $matrix
	 */
	public function __construct(ContainerInterface $options, QRMatrix $matrix){
		$this->options     = $options;
		$this->matrix      = $matrix;
		$this->moduleCount = $this->matrix->size();

		$class = get_called_class();

		if(array_key_exists($class, QRCode::OUTPUT_MODES) && in_array($this->options->outputType, QRCode::OUTPUT_MODES[$class])){
			$this->outputMode = $this->options->outputType;
		}

	}

	/**
	 * @see file_put_contents()
	 *
	 * @param string $data
	 *
	 * @return bool|int
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function saveToFile(string $data) {

		if(!is_writable(dirname($this->options->cachefile))){
			throw new QRCodeOutputException('Could not write data to cache file: '.$this->options->cachefile);
		}

		return file_put_contents($this->options->cachefile, $data);
	}

	/**
	 * @return string
	 */
	public function dump(){
		$data = call_user_func([$this, $this->outputMode ?? $this->defaultMode]);

		if($this->options->cachefile !== null){
			$this->saveToFile($data);
		}

		return $data;
	}

}
