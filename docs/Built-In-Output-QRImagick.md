# QRImagick

[Class `QRImagick`](https://github.com/chillerlan/php-qrcode/blob/main/src/Output/QRImagick.php): [ImageMagick](https://www.php.net/manual/book.imagick) output, [multiple supported image formats](https://imagemagick.org/script/formats.php)

Please follow the installation guides for your operating system:

- ImageMagick: [imagemagick.org/script/download.php](https://imagemagick.org/script/download.php)
- PHP `ext-imagick`: [github.com/Imagick/imagick](https://github.com/Imagick/imagick) ([Windows downloads](https://mlocati.github.io/articles/php-windows-imagick.html))
- [PHP Imagick by Example](https://phpimagick.com/) ([github.com/Imagick/ImagickDemos](https://github.com/Imagick/ImagickDemos))


## Options that affect this module

| property                       | type           |
|--------------------------------|----------------|
| `$returnResource`              | `bool`         |
| `$imageBase64`                 | `bool`         |
| `$bgColor`                     | `mixed`        |
| `$drawLightModules`            | `bool`         |
| `$drawCircularModules`         | `bool`         |
| `$circleRadius`                | `float`        |
| `$keepAsSquare`                | `array`        |
| `$scale`                       | `int`          |
| `$imageTransparent`            | `bool`         |
| `$transparencyColor`           | `mixed`        |
| `$imagickFormat`               | `string`       |


### Options that have no effect

| property                       | reason            |
|--------------------------------|-------------------|
| `$connectPaths`                | N/A               |
| `$excludeFromConnect`          | N/A               |
| `$pngCompression`              | GdImage exclusive |
| `$jpegQuality`                 | GdImage exclusive |
