<?php
/**
 * Class BenchmarkAbstract
 *
 * @created      23.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeBenchmark;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\{EccLevel, Mode, Version};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCodeTest\QRMaxLengthTrait;
use PhpBench\Attributes\{Iterations, ParamProviders, Revs, Warmup};
use Generator, RuntimeException;
use function extension_loaded, is_dir, mb_substr, mkdir, sprintf, str_repeat, str_replace;

/**
 * The abstract benchmark with common methods
 */
#[Iterations(3)]
#[Warmup(3)]
#[Revs(100)]
#[ParamProviders(['versionProvider', 'eccLevelProvider', 'dataModeProvider'])]
abstract class BenchmarkAbstract{
	use QRMaxLengthTrait;

	protected const BUILDDIR    = __DIR__.'/../.build/phpbench/';
	protected const ECC_LEVELS  = [EccLevel::L, EccLevel::M, EccLevel::Q, EccLevel::H];
	protected const DATAMODES   = Mode::INTERFACES;

	/** @var array<int, string> */
	protected array     $dataModeData;
	protected string    $testData;
	protected QROptions $options;
	protected QRMatrix  $matrix;

	// properties from data providers
	protected Version   $version;
	protected EccLevel  $eccLevel;
	protected int       $mode;
	protected string    $modeFQCN;

	/**
	 * @throws \RuntimeException
	 */
	public function __construct(){

		foreach(['gd', 'imagick'] as $ext){
			if(!extension_loaded($ext)){
				throw new RuntimeException(sprintf('ext-%s not loaded', $ext));
			}
		}

		if(!is_dir(self::BUILDDIR)){
			mkdir(directory: self::BUILDDIR, recursive: true);
		}

		$this->dataModeData = $this->generateDataModeData();
	}

	/**
	 * Generates test data strings for each mode
	 *
	 * @return array<int, string>
	 */
	protected function generateDataModeData():array{
		return [
			Mode::NUMBER   => str_repeat('0123456789', 750),
			Mode::ALPHANUM => str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', 100),
			Mode::KANJI    => str_repeat('漂う花の香り', 350),
			Mode::HANZI    => str_repeat('无可奈何燃花作香', 250),
			Mode::BYTE     => str_repeat('https://www.youtube.com/watch?v=dQw4w9WgXcQ ', 100),
		];
	}

	/**
	 * Generates a test max-length data string for the given version, ecc level and data mode
	 */
	protected function getData(Version $version, EccLevel $eccLevel, int $mode):string{
		$maxLength = self::getMaxLengthForMode($mode, $version, $eccLevel);

		if($mode === Mode::KANJI || $mode === Mode::HANZI){
			return mb_substr($this->dataModeData[$mode], 0, $maxLength);
		}

		return mb_substr($this->dataModeData[$mode], 0, $maxLength, '8bit');
	}

	/**
	 * Initializes a QROptions instance and assigns it to its temp property
	 *
	 * @param array<string, mixed> $options
	 */
	protected function initQROptions(array $options):void{
		$this->options = new QROptions($options);
	}

	/**
	 * Initializes a QRMatrix instance and assigns it to its temp property
	 */
	public function initMatrix():void{
		$this->matrix = (new QRCode($this->options))
			->addByteSegment($this->testData)
			->getQRMatrix()
		;
	}

	/**
	 * Generates a test data string and assigns it to its temp property
	 */
	public function generateTestData():void{
		$this->testData = $this->getData($this->version, $this->eccLevel, $this->mode);
	}

	/**
	 * Assigns the parameter array from the providers to properties and enforces the types
	 *
	 * @param array<string, mixed> $params
	 */
	public function assignParams(array $params):void{
		foreach($params as $k => $v){
			$this->{$k} = $v;
		}
	}

	public function versionProvider():Generator{
		// run all versions between 1 and 10 as they're the most commonly used
		for($v = 1; $v <= 10; $v++){
			yield (string)$v => ['version' => new Version($v)];
		}
		// 15-40 in steps of 5
		for($v = 15; $v <= 40; $v += 5){
			yield (string)$v => ['version' => new Version($v)];
		}
	}

	public function eccLevelProvider():Generator{
		foreach(static::ECC_LEVELS as $ecc){
			$eccLevel = new EccLevel($ecc);

			yield (string)$eccLevel => ['eccLevel' => $eccLevel];
		}
	}

	public function dataModeProvider():Generator{
		foreach(static::DATAMODES as $mode => $modeFQCN){
			$name = str_replace('chillerlan\\QRCode\\Data\\', '', $modeFQCN);

			yield $name => ['mode' => $mode, 'modeFQCN' => $modeFQCN];
		}
	}

}
