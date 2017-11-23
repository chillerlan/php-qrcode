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
	Data\QRMatrix, QROptions
};

/**
 *
 */
abstract class QROutputAbstract implements QROutputInterface{

	/**
	 * @param \chillerlan\QRCode\Data\QRMatrix $matrix
	 */
	protected $matrix;

	/**
	 * @var int
	 */
	protected $moduleCount;

	/**
	 * @var object
	 */
	protected $options;

	/**
	 * @param \chillerlan\QRCode\QROptions      $options
	 * @param \chillerlan\QRCode\Data\QRMatrix  $matrix
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function __construct(QROptions $options, QRMatrix $matrix){
		$this->options = $options;

		$this->moduleCount = $matrix->size();

		if($this->moduleCount < 21){  // minimum QR modules @todo: quet zone
			throw new QRCodeOutputException('Invalid matrix!');
		}

		$this->matrix = $matrix;
	}

	/**
	 * @param string $data
	 *
	 * @return bool
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function saveToFile(string $data):bool {

		try{
			return (bool)file_put_contents($this->options->cachefile, $data);
		}
		catch(\Exception $e){
			throw new QRCodeOutputException('Could not write data to cache file: '.$e->getMessage());
		}

	}

}
