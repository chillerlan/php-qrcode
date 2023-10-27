# QRImagick

[Class `QRImagick`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRImagick.php): [ImageMagick](https://www.php.net/manual/book.imagick) output, [multiple supported image formats](https://imagemagick.org/script/formats.php)

Please follow the installation guides for your operating system:

- ImageMagick: [imagemagick.org/script/download.php](https://imagemagick.org/script/download.php)
- PHP `ext-imagick`: [github.com/Imagick/imagick](https://github.com/Imagick/imagick) ([Windows downloads](https://mlocati.github.io/articles/php-windows-imagick.html))
- [PHP Imagick by Example](https://phpimagick.com/) ([github.com/Imagick/ImagickDemos](https://github.com/Imagick/ImagickDemos))


## Example

See: [ImageMagick example](https://github.com/chillerlan/php-qrcode/blob/main/examples/imagick.php)

Set the options:
```php
$options = new QROptions;

$options->outputType          = QROutputInterface::IMAGICK;
$options->imagickFormat       = 'webp'; // e.g. png32, jpeg, webp
$options->quality             = 90;
$options->scale               = 20;
$options->bgColor             = '#ccccaa';
$options->imageTransparent    = true;
$options->transparencyColor   = '#ccccaa';
$options->drawLightModules    = true;
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
$out  = (new QRCode($options))->render($data); // -> data:image/webp;base64,...

printf('<img alt="%s" src="%s" />', $alt, $out);
```


Return the `Imagick` instance (will ignore other output options):

```php
$options->returnResource = true;

/** @var \Imagick $imagick */
$imagick = (new QRCode($options))->render($data);

// do stuff with the Imagick instance...
$imagick->scaleImage(150, 150, true);
// ...

// ...dump output
$imagick->setImageFormat('png32');

header('Content-type: image/png');

echo $imagick->getImageBlob();
```


## Additional methods

| method                               | return | description                                |
|--------------------------------------|--------|--------------------------------------------|
| (protected) `drawImage()`            | `void` | Creates the QR image via ImagickDraw       |
| (protected) `module()`               | `void` | Draws a single pixel at the given position |
| (protected) `setBgColor()`           | `void` | Sets the background color                  |
| (protected) `setTransparencyColor()` | `void` | Sets the transparency color                |


## Options that affect this module

| property               | type     |
|------------------------|----------|
| `$bgColor`             | `mixed`  |
| `$circleRadius`        | `float`  |
| `$drawCircularModules` | `bool`   |
| `$drawLightModules`    | `bool`   |
| `$imageTransparent`    | `bool`   |
| `$imagickFormat`       | `string` |
| `$keepAsSquare`        | `array`  |
| `$outputBase64`        | `bool`   |
| `$quality`             | `int`    |
| `$returnResource`      | `bool`   |
| `$scale`               | `int`    |
| `$transparencyColor`   | `mixed`  |


### Options that have no effect

| property              | reason |
|-----------------------|--------|
| `$connectPaths`       | N/A    |
| `$excludeFromConnect` | N/A    |
