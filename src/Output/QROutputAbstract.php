<?php
/**
 * Class QROutputAbstract
 *
 * @created      09.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\Settings\SettingsContainerInterface;
use Closure;
use function base64_encode, dirname, file_put_contents, is_writable, ksort, sprintf;

/**
 * common output abstract
 */
abstract class QROutputAbstract implements QROutputInterface{

	/**
	 * the current size of the QR matrix
	 *
	 * @see \chillerlan\QRCode\Data\QRMatrix::size()
	 */
	protected int $moduleCount;

	/**
	 * the current scaling for a QR pixel
	 *
	 * @see \chillerlan\QRCode\QROptions::$scale
	 */
	protected int $scale;

	/**
	 * the side length of the QR image (modules * scale)
	 */
	protected int $length;

	/**
	 * an (optional) array of color values for the several QR matrix parts
	 */
	protected array $moduleValues;

	/**
	 * the (filled) data matrix object
	 */
	protected QRMatrix $matrix;

	/**
	 * @var \chillerlan\Settings\SettingsContainerInterface|\chillerlan\QRCode\QROptions
	 */
	protected SettingsContainerInterface $options;

	/**
	 * QROutputAbstract constructor.
	 */
	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){
		$this->options     = $options;
		$this->matrix      = $matrix;
		$this->moduleCount = $this->matrix->size();
		$this->scale       = $this->options->scale;
		$this->length      = $this->moduleCount * $this->scale;

		$this->setModuleValues();
	}

	/**
	 * Sets the initial module values
	 */
	protected function setModuleValues():void{

		foreach($this::DEFAULT_MODULE_VALUES as $M_TYPE => $defaultValue){
			$value = $this->options->moduleValues[$M_TYPE] ?? null;

			$this->moduleValues[$M_TYPE] = $this->moduleValueIsValid($value)
				? $this->getModuleValue($value)
				: $this->getDefaultModuleValue($defaultValue);
		}

	}

	/**
	 * Determines whether the given value is valid
	 *
	 * @param mixed|null $value
	 */
	abstract protected function moduleValueIsValid($value):bool;

	/**
	 * Returns the final value for the given input (return value depends on the output module)
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	abstract protected function getModuleValue($value);

	/**
	 * Returns a defualt value for either dark or light modules (return value depends on the output module)
	 *
	 * @return mixed
	 */
	abstract protected function getDefaultModuleValue(bool $isDark);

	/**
	 * Returns a base64 data URI for the given string and mime type
	 */
	protected function base64encode(string $data, string $mime):string{
		return sprintf('data:%s;base64,%s', $mime, base64_encode($data));
	}

	/**
	 * saves the qr data to a file
	 *
	 * @see file_put_contents()
	 * @see \chillerlan\QRCode\QROptions::cachefile
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function saveToFile(string $data, string $file):void{

		if(!is_writable(dirname($file))){
			throw new QRCodeOutputException(sprintf('Cannot write data to cache file: %s', $file));
		}

		if(file_put_contents($file, $data) === false){
			throw new QRCodeOutputException(sprintf('Cannot write data to cache file: %s (file_put_contents error)', $file));
		}
	}

	/**
	 * collects the modules per QRMatrix::M_* type and runs a $transform functio on each module and
	 * returns an array with the transformed modules
	 */
	protected function collectModules(Closure $transform):array{
		$paths = [];

		// collect the modules for each type
		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $M_TYPE){

				if($this->options->connectPaths && !$this->matrix->checkTypes($x, $y, $this->options->excludeFromConnect)){
					// to connect paths we'll redeclare the $M_TYPE to data only
					$M_TYPE = QRMatrix::M_DATA;

					if($this->matrix->check($x, $y)){
						$M_TYPE |= QRMatrix::IS_DARK;
					}
				}

				// collect the modules per $M_TYPE
				$paths[$M_TYPE][] = $transform($x, $y);
			}
		}

		// beautify output
		ksort($paths);

		return $paths;
	}

}
