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

use chillerlan\QRCode\{Data\QRMatrix, QRCode};
use chillerlan\Settings\SettingsContainerInterface;

use function call_user_func, dirname, file_put_contents, get_called_class, in_array, is_writable, sprintf;

/**
 * common output abstract
 */
abstract class QROutputAbstract implements QROutputInterface{

	protected int $moduleCount;

	protected QRMatrix $matrix;
	/** @var \chillerlan\Settings\SettingsContainerInterface|\chillerlan\QRCode\QROptions */
	protected SettingsContainerInterface $options;

	protected string $outputMode;

	protected string $defaultMode;

	protected int $scale;

	protected int $length;

	protected array $moduleValues;

	/**
	 * QROutputAbstract constructor.
	 */
	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){
		$this->options     = $options;
		$this->matrix      = $matrix;
		$this->moduleCount = $this->matrix->size();
		$this->scale       = $this->options->scale;
		$this->length      = $this->moduleCount * $this->scale;

		$class = get_called_class();

		if(isset(QRCode::OUTPUT_MODES[$class]) && in_array($this->options->outputType, QRCode::OUTPUT_MODES[$class])){
			$this->outputMode = $this->options->outputType;
		}

		$this->setModuleValues();
	}

	/**
	 * Sets the initial module values (clean-up & defaults)
	 */
	abstract protected function setModuleValues():void;

	/**
	 * @see file_put_contents()
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function saveToFile(string $data, string $file):bool{

		if(!is_writable(dirname($file))){
			throw new QRCodeOutputException(sprintf('Could not write data to cache file: %s', $file));
		}

		return (bool)file_put_contents($file, $data);
	}

	/**
	 *
	 */
	public function dump(string $file = null){
		$data = call_user_func([$this, $this->outputMode ?? $this->defaultMode]);
		$file ??= $this->options->cachefile;

		if($file !== null){
			$this->saveToFile($data, $file);
		}

		return $data;
	}

}
