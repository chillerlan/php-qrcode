# QRGdImage

[Class `QRGdImage`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRGdImage.php):
[GdImage](https://www.php.net/manual/book.image) raster graphic output (GIF, JPG, PNG, ...)


## Example

See: [GdImage example](https://github.com/chillerlan/php-qrcode/blob/main/examples/image.php)

Set the options:

```php
$options = new QROptions;

// $outputInterface can be one of the classes listed in `QROutputInterface::MODES`
$options->outputInterface     = QRGdImageWEBP::class;
$options->quality             = 90;
// the size of one qr module in pixels
$options->scale               = 20;
$options->bgColor             = [200, 150, 200];
$options->imageTransparent    = true;
// the color that will be set transparent
// @see https://www.php.net/manual/en/function.imagecolortransparent
$options->transparencyColor   = [200, 150, 200];
$options->drawCircularModules = true;
$options->drawLightModules    = true;
$options->circleRadius        = 0.4;
$options->keepAsSquare        = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];
$options->moduleValues        = [
	QRMatrix::M_FINDER_DARK    => [0, 63, 255], // dark (true)
	QRMatrix::M_FINDER_DOT     => [0, 63, 255], // finder dot, dark (true)
	QRMatrix::M_FINDER         => [233, 233, 233], // light (false)
	QRMatrix::M_ALIGNMENT_DARK => [255, 0, 255],
	QRMatrix::M_ALIGNMENT      => [233, 233, 233],
	QRMatrix::M_DATA_DARK      => [0, 0, 0],
	QRMatrix::M_DATA           => [233, 233, 233],
];
```

Render the output:

```php
$data = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$out  = (new QRCode($options))->render($data); // -> data:image/webp;base64,...

printf('<img alt="%s" src="%s" />', $alt, $out);
```


Return the `GdImage` instance (will ignore other output options):

```php
$options->returnResource = true;

/** @var \GdImage $gdImage */
$gdImage = (new QRCode($options))->render($data);

// do stuff with the GdImage instance...
$size = imagesx($gdImage);
// ...

// ...dump output
header('Content-type: image/jpeg');

imagejpeg($gdImage);
imagedestroy($gdImage);
```


## Additional methods

| method                                            | return   | description                                                                                      |
|---------------------------------------------------|----------|--------------------------------------------------------------------------------------------------|
| (protected) `drawImage()`                         | `void`   | Draws the QR image                                                                               |
| (protected) `dumpImage()`                         | `string` | Creates the final image by calling the desired GD output function                                |
| (protected) `module(int $x, int $y, int $M_TYPE)` | `void`   | Renders a single module                                                                          |
| (protected) `setBgColor()`                        | `void`   | Sets the background color                                                                        |
| (protected) `setTransparencyColor()`              | `void`   | Sets the transparency color                                                                      |
| (abstract protected) `renderImage()`              | `void`   | Renders the image with the gdimage function for the desired output, implemented by child classes |


## Options that affect this module

| property               | type           |
|------------------------|----------------|
| `$bgColor`             | `mixed`        |
| `$circleRadius`        | `float`        |
| `$drawCircularModules` | `bool`         |
| `$drawLightModules`    | `bool`         |
| `$imageTransparent`    | `bool`         |
| `$quality`             | `int`          |
| `$keepAsSquare`        | `array`        |
| `$outputBase64`        | `bool`         |
| `$returnResource`      | `bool`         |
| `$scale`               | `int`          |
| `$transparencyColor`   | `mixed`        |
