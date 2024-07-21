<?php
/**
 * Class QRInterventionImage
 *
 * @created      21.01.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QROptions;
use chillerlan\Settings\SettingsContainerInterface;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Geometry\Factories\CircleFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Interfaces\ImageManagerInterface;
use UnhandledMatchError;
use function class_exists;
use function extension_loaded;
use function intdiv;

/**
 * intervention/image (GD/ImageMagick) output
 *
 * note: this output class works very slow compared to the native GD/Imagick output classes for obvious reasons.
 *       use only if you must.
 *
 * @see https://github.com/Intervention/image
 * @see https://image.intervention.io/
 */
class QRInterventionImage extends QROutputAbstract{
	use CssColorModuleValueTrait;

	protected DriverInterface $driver;
	protected ImageManagerInterface $manager;
	protected ImageInterface $image;

	/**
	 * QRInterventionImage constructor.
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function __construct(SettingsContainerInterface|QROptions $options, QRMatrix $matrix){

		if(!class_exists(ImageManager::class)){
			// @codeCoverageIgnoreStart
			throw new QRCodeOutputException(
				'The QRInterventionImage output requires Intervention/image (https://github.com/Intervention/image)'.
				' as dependency but the class "\\Intervention\\Image\\ImageManager" could not be found.',
			);
			// @codeCoverageIgnoreEnd
		}

		try{
			$this->driver = match(true){
				extension_loaded('gd')      => new GdDriver,
				extension_loaded('imagick') => new ImagickDriver,
			};

			$this->setDriver($this->driver);
		}
		catch(UnhandledMatchError){
			throw new QRCodeOutputException('no image processing extension loaded (gd, imagick)'); // @codeCoverageIgnore
		}

		parent::__construct($options, $matrix);
	}

	/**
	 * Sets a DriverInterface
	 */
	public function setDriver(DriverInterface $driver):static{
		$this->manager = new ImageManager($driver);

		return $this;
	}

	public function dump(string|null $file = null):string|ImageInterface{
		[$width, $height] = $this->getOutputDimensions();

		$this->image = $this->manager->create($width, $height);

		$this->image->fill($this->getDefaultModuleValue(false));

		if($this->options->imageTransparent && $this::moduleValueIsValid($this->options->transparencyColor)){
			$this->manager
				->driver()
				->config()
				->setOptions(blendingColor: $this->prepareModuleValue($this->options->transparencyColor))
			;
		}

		if($this::moduleValueIsValid($this->options->bgColor)){
			$this->image->fill($this->prepareModuleValue($this->options->bgColor));
		}

		foreach($this->matrix->getMatrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$this->module($x, $y, $M_TYPE);
			}
		}

		if($this->options->returnResource){
			return $this->image;
		}

		$image     = $this->image->toPng();
		$imageData = $image->toString();

		$this->saveToFile($imageData, $file);

		if($this->options->outputBase64){
			return $image->toDataUri();
		}

		return $imageData;
	}

	/**
	 * draws a single pixel at the given position
	 */
	protected function module(int $x, int $y, int $M_TYPE):void{

		if(!$this->drawLightModules && !$this->matrix->isDark($M_TYPE)){
			return;
		}

		$color = $this->getModuleValue($M_TYPE);

		if($this->drawCircularModules && !$this->matrix->checkTypeIn($x, $y, $this->keepAsSquare)){

			$this->image->drawCircle(
				(($x * $this->scale) + intdiv($this->scale, 2)),
				(($y * $this->scale) + intdiv($this->scale, 2)),
				function(CircleFactory $circle) use ($color):void{
					$circle->radius((int)($this->circleRadius * $this->scale));
					$circle->background($color);
				},
			);

			return;
		}

		$this->image->drawRectangle(
			($x * $this->scale),
			($y * $this->scale),
			function(RectangleFactory $rectangle) use ($color):void{
				$rectangle->size($this->scale, $this->scale);
				$rectangle->background($color);
			},
		);
	}

}
