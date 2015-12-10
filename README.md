# codemasher/php-qrcode

[![Packagist](https://img.shields.io/packagist/v/chillerlan/php-qrcode.svg?style=flat-square)](https://packagist.org/packages/chillerlan/php-qrcode)
[![License](https://img.shields.io/packagist/l/chillerlan/php-qrcode.svg?style=flat-square)](LICENSE)

## Requirements
- PHP 5.6+, PHP 7

## Documentation

### Installation
#### Using [composer](https://getcomposer.org)

*Terminal*
```sh
composer require chillerlan/php-qrcode:dev-master
```

*composer.json*
```json
{
	"require": {
		"php": ">=5.6.0",
		"chillerlan/php-qrcode": "dev-master"
	}
}
```

#### Manual installation
Download the desired version of the package from [master](https://github.com/codemasher/php-qrcode/archive/master.zip) or 
[release](https://github.com/codemasher/php-qrcode/releases) and extract the contents to your project folder. 
Point the namespace `chillerlan\QRCode` to the folder `src` of the package.

Profit!

### Usage
We want to encode this data into a QRcode image:
```php
$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';
```

Quick and simple:
```php
echo '<img src="'.(new QRCode($data, new QRImage))->output().'" />';
```

Wait, what was that? Please again, slower!

Ok, step by step. You'll need a `QRCode` instance which needs to be invoked with the data and a `Output\QROutputInterface` as parameters.
The `QRCode` and `QROutputInterface` classes can be optionally invoked with a `QROptions` or a `Output\QR*Options` Object.
```php
$qrOptions = new QROptions;
$qrOptions->errorCorrectLevel = QRCode::ERROR_CORRECT_LEVEL_L;

// image...
$outputOptions = new QRImageOptions;
$outputOptions->type = QRCode::OUTPUT_IMAGE_GIF;
$outputInterface = new QRImage($outputOptions);

// ...or string
$outputOptions = new QRStringOptions;
$outputOptions->type = QRCode::OUTPUT_STRING_HTML;
$outputInterface = new QRString($outputOptions);

$qrcode = new QRCode($data, $outputInterface, $qrOptions);
```

Have a look [in this folder](https://github.com/codemasher/php-qrcode/tree/master/examples) for some usage examples.

### Docs
Here you'll find a list of the possible values for `QROptions` and `Output\QR*Options` along with their defaults.

```php
// error correct level: L (7%),  M (15%), Q (25%), H (30%)
QROptions::$errorCorrectLevel = QRCode::ERROR_CORRECT_LEVEL_M;
// type number, null = auto, QRCode::TYPE_01 -> QRCode::TYPE_10
QROptions::$typeNumber        = null; QRCode::TYPE_01;

// output sting type: QRCode::OUTPUT_STRING_TEXT/JSON/HTML
QRStringOptions::$type        = QRCode::OUTPUT_STRING_TEXT;
// string substitutes for dark & light
QRStringOptions::$textDark    = '#';
QRStringOptions::$textLight   = ' ';
// newline string
QRStringOptions::$textNewline = PHP_EOL;

// output image type: QRCode::OUTPUT_IMAGE_PNG/JPG/GIF
QRImageOptions::$type           = QRCode::OUTPUT_IMAGE_PNG;
// return as base64
QRImageOptions::$base64         = true;
// optional cache file path, null returns the image data
QRImageOptions::$cachefile      = null;
// size settings
QRImageOptions::$pixelSize      = 5;
QRImageOptions::$marginSize     = 5;
//color settings
QRImageOptions::$transparent    = true;
QRImageOptions::$fgRed          = 0;
QRImageOptions::$fgGreen        = 0;
QRImageOptions::$fgBlue         = 0;
QRImageOptions::$bgRed          = 255;
QRImageOptions::$bgGreen        = 255;
QRImageOptions::$bgBlue         = 255;
// imagepng()/imagegif() quality settings
QRImageOptions::$pngCompression = -1;
QRImageOptions::$jpegQuality    = 85;

```
