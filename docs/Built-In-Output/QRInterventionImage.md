# QRInterventionImage

[Class `QRInterventionImage`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRInterventionImage.php):
[intervention/image](https://image.intervention.io/) alternative GD/ImageMagick output.

***Note:** this output class works significantly slower than the native GD/Imagick output classes due to the several underlying abstraction layers. Use only if you must.*


## Example

See: [intervention/image example](https://github.com/chillerlan/php-qrcode/blob/main/examples/intervention-image.php)

Set the options:

```php
$options = new QROptions;

$options->outputInterface     = QRInterventionImage::class;
$options->scale               = 20;
$options->bgColor             = '#ccccaa';
$options->imageTransparent    = false;
$options->transparencyColor   = '#ccccaa';
$options->drawLightModules    = false;
$options->drawCircularModules = true;
$options->circleRadius        = 0.4;
$options->keepAsSquare        = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];
$options->moduleValues        = [
	QRMatrix::M_FINDER_DARK    => '#A71111', // dark (true)
	QRMatrix::M_FINDER_DOT     => '#A71111', // finder dot, dark (true)
	QRMatrix::M_FINDER         => '#FFBFBF', // light (false)
	QRMatrix::M_ALIGNMENT_DARK => '#A70364',
	QRMatrix::M_ALIGNMENT      => '#FFC9C9',
	QRMatrix::M_VERSION_DARK   => '#650098',
	QRMatrix::M_VERSION        => '#E0B8FF',
];
```


Render the output:

```php
$data = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$out  = (new QRCode($options))->render($data); // -> data:image/png;base64,...

printf('<img alt="%s" src="%s" />', $alt, $out);
```


Return the `ImageInterface` instance (will ignore other output options):

```php
$options->returnResource = true;

/** @var \Intervention\Image\Interfaces\ImageInterface $image */
$image = (new QRCode($options))->render($data);

// do stuff with the ImageInterface instance...

// ...dump output

header('Content-type: image/png');

echo $image->toPng()->toString();
```

Set a different driver in the internal `ImageManager` instance (the internal detection order is: 1. GD, 2. Imagick):

```php
$qrOutputInterface = new QRInterventionImage($options, $matrix);
// set a different driver
$qrOutputInterface->setDriver(new \Intervention\Image\Drivers\Imagick\Driver);
// dump output
$out = $qrOutputInterface->dump();
```


## Additional methods

| method                               | return   | description                                                                                                          |
|--------------------------------------|----------|----------------------------------------------------------------------------------------------------------------------|
| `setDriver(DriverInterface $driver)` | `static` | Sets a DriverInterface, see [instantiation (intervention.io)](https://image.intervention.io/v3/basics/instantiation) |
| (protected) `module()`               | `void`   | Draws a single pixel at the given position                                                                           |


## Options that affect this module

| property               | type     |
|------------------------|----------|
| `$bgColor`             | `mixed`  |
| `$circleRadius`        | `float`  |
| `$drawCircularModules` | `bool`   |
| `$drawLightModules`    | `bool`   |
| `$imageTransparent`    | `bool`   |
| `$keepAsSquare`        | `array`  |
| `$outputBase64`        | `bool`   |
| `$returnResource`      | `bool`   |
| `$scale`               | `int`    |
| `$transparencyColor`   | `mixed`  |
