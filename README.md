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
// 10 reasons why QR codes are awesome
$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

// no, for serious, we want to display a QR code for a mobile authenticator
// https://github.com/codemasher/php-googleauth
$data = 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net';
```

Quick and simple:
```php
echo '<img src="'.(new QRCode($data, new QRImage))->output().'" />';
```

Wait, what was that? Please again, slower!

Ok, step by step. You'll need a `QRCode` instance which needs to be invoked with the data and a `Output\QROutputInterface` as parameters.
```php
// the built-in QROutputInterface classes
$outputInterface = new QRImage;
$outputInterface = new QRString;

// invoke a fresh QRCode instance
$qrcode = new QRCode($data, $outputInterface);

// and dump the output
$qrcode->output();
```

The `QRCode` and built-in `QROutputInterface` classes can be optionally invoked with a `QROptions` or a `Output\QR*Options` Object respectively.
```php
// image
$outputOptions = new QRImageOptions;
$outputOptions->type = QRCode::OUTPUT_IMAGE_GIF;
$outputInterface = new QRImage($outputOptions);

// string
$outputOptions = new QRStringOptions;
$outputOptions->type = QRCode::OUTPUT_STRING_HTML;
$outputInterface = new QRString($outputOptions);

// QRCode
$qrOptions = new QROptions;
$qrOptions->errorCorrectLevel = QRCode::ERROR_CORRECT_LEVEL_L;

$qrcode = new QRCode($data, $outputInterface, $qrOptions);
```

Have a look [in this folder](https://github.com/codemasher/php-qrcode/tree/master/examples) for some usage examples.

### Advanced usage

Here you'll find a list of the possible values for `QROptions` and `Output\QR*Options` along with their defaults.

####  Properties of `QROptions`

property | type | default | allowed | description
-------- | ---- | ------- | ------- | -----------
$errorCorrectLevel | int | M | QRCode::ERROR_CORRECT_LEVEL_X | X = error correct level: L (7%),  M (15%), Q (25%), H (30%)
$typeNumber | int | null | QRCode::TYPE_XX | type number, null = auto, XX = 01 ... 10

####  Properties of `QRStringOptions`

property | type | default | allowed | description
-------- | ---- | ------- | ------- | -----------
$type | int | HTML | QRCode::OUTPUT_STRING_XXXX | XXXX = TEXT, JSON, HTML
$textDark | string | '#' | * | string substitute for dark
$textLight | string | ' ' | * | string substitute for light
$textNewline | string | PHP_EOL | * | newline string
$htmlRowTag | string | 'p' | * | the shortest available semanically correct row (block) tag to not bloat the output
$htmlOmitEndTag | bool | true | - | the closing <p> tag may be omitted (moar bloat!)

####  Properties of `QRImageOptions`

property | type | default | allowed | description
-------- | ---- | ------- | ------- | -----------
$type | string | PNG | QRCode::OUTPUT_IMAGE_XXX | output image type, XXX = PNG, JPG, GIF
$base64 | bool | true | - | wether to return the image data as base64 or raw as from `file_get_contents()`
$cachefile | string | null | * | optional cache file path, null returns the image data
$pixelSize | int | 5 | 1 ... 25 | 
$marginSize | int | 5 | 0 ... 25 | 
$transparent | bool | true | - | 
$fgRed | int | 0 | 0 ... 255 | 
$fgGreen | int | 0 | 0 ... 255 | 
$fgBlue | int | 0 | 0 ... 255 | 
$bgRed | int | 255 | 0 ... 255 | 
$bgGreen | int | 255 | 0 ... 255 | 
$bgBlue | int | 255 | 0 ... 255 | 
$pngCompression | int | -1 | -1 ... 9 | `imagepng()` compression level
$jpegQuality | int | 85 | 0 - 100 | `imagejpeg()` quality
