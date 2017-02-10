# codemasher/php-qrcode

[![version][packagist-badge]][packagist]
[![license][license-badge]][license]
[![Travis][travis-badge]][travis]
[![Coverage][coverage-badge]][coverage]
[![Issues][issue-badge]][issues]
[![Scrunitizer][scrutinizer-badge]][scrutinizer]
[![Code Climate][codeclimate-badge]][codeclimate]

[packagist-badge]: https://img.shields.io/packagist/v/chillerlan/php-qrcode.svg
[packagist]: https://packagist.org/packages/chillerlan/php-qrcode
[license-badge]: https://img.shields.io/packagist/l/chillerlan/php-qrcode.svg
[license]: https://github.com/codemasher/php-qrcode/blob/master/LICENSE
[travis-badge]: https://travis-ci.org/codemasher/php-qrcode.svg?branch=master
[travis]: https://travis-ci.org/codemasher/php-qrcode
[coverage-badge]: https://codecov.io/github/codemasher/php-qrcode/coverage.svg?branch=master
[coverage]: https://codecov.io/github/codemasher/php-qrcode
[issue-badge]: https://img.shields.io/github/issues/codemasher/php-qrcode.svg
[issues]: https://github.com/codemasher/php-qrcode/issues
[scrutinizer-badge]: https://scrutinizer-ci.com/g/codemasher/php-qrcode/badges/quality-score.png?b=master
[scrutinizer]: https://scrutinizer-ci.com/g/codemasher/php-qrcode
[codeclimate-badge]: https://codeclimate.com/github/codemasher/php-qrcode/badges/gpa.svg
[codeclimate]: https://codeclimate.com/github/codemasher/php-qrcode

## Info

This library is based on the [QR code implementation](https://github.com/kazuhikoarase/qrcode-generator) by [Kazuhiko Arase](https://github.com/kazuhikoarase), 
namespaced, cleaned up, made extensible and PHP7 ready (among other stuff). The main intend is to use it along with a [Google authenticator implementation](https://github.com/codemasher/php-googleauth).

## Requirements
- PHP 7+

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
		"php": ">=7.0.3",
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

<p align="center">
  <a href="https://www.turnon2fa.com">
    <img alt="QR codes are awesome!" src="https://raw.githubusercontent.com/codemasher/php-qrcode/master/examples/example_image.png">
  </a>
</p>

Wait, what was that? Please again, slower!


### Advanced usage

Ok, step by step. You'll need a `QRCode` instance which needs to be invoked with the data and a `Output\QROutputInterface` as parameters.
```php
// the built-in QROutputInterface classes
$outputInterface = new QRImage;
$outputInterface = new QRMarkup;
$outputInterface = new QRString;

// invoke a fresh QRCode instance
$qrcode = new QRCode($data, $outputInterface);

// and dump the output
$qrcode->output();
```

Have a look [in this folder](https://github.com/codemasher/php-qrcode/tree/master/examples) for some usage examples.

The `QRCode` and built-in `QROutputInterface` classes can be optionally invoked with a `QROptions` or a `Output\QROutputOptionsInterface` Object respectively.
```php
// image -> QRImageOptions
$outputOptions = new QRImageOptions;
$outputOptions->type = QRCode::OUTPUT_IMAGE_GIF;
$outputInterface = new QRImage($outputOptions);

// string -> QRStringOptions
$outputOptions = new QRStringOptions;
$outputOptions->type = QRCode::OUTPUT_STRING_TEXT;
$outputInterface = new QRString($outputOptions);

// QROptions
$qrOptions = new QROptions;
$qrOptions->errorCorrectLevel = QRCode::ERROR_CORRECT_LEVEL_L;

$qrcode = new QRCode($data, $outputInterface, $qrOptions);
```

You can reuse the `QRCode` object once created in case you don't need to change the output, and use the `QRCode::setData()` method instead.
```php
$qrcode->setData($data);
$qrcode->setData($data, $qrOptions);

$qrcode->output();
```

In case you only want the raw array which represents the QR code matrix, just call `QRCode::getRawData()` - this method is also called internally from `QRCode::output()`.
```php
$matrix = $qrcode->getRawData();

foreach($matrix as $row){
	foreach($row as $dark){
		if($dark){
			// do stuff
		}
		else{
			// do other stuff
		}
	}
}

```

### Custom output modules
But then again, instead of bloating your own code, you can simply create your own output module by extending `QROutputAbstract`.
```php
$qrcode = new QRCode($data, new MyCustomOutput($myCustomOutputOptions), $qrOptions)
```

```php
class MyCustomOutput extends QROutputAbstract{
	
	// inherited from QROutputAbstract
	protected $matrix; // array
	protected $pixelCount; // int
	protected $options; // MyCustomOutputOptions (if present)
	
	// optional constructor
	public function __construct(QROutputOptionsInterface $outputOptions = null){
		$this->options = $outputOptions;

		if(!$this->options){
			// MyCustomOutputOptions should supply default values
			$this->options = new MyCustomOutputOptions;
		}

	}

	public function dump(){
	
		$output = '';

		for($row = 0; $row < $this->pixelCount; $row++){
			for($col = 0; $col < $this->pixelCount; $col++){
				$output .= (string)(int)$this->matrix[$row][$col];
			}
		}

		return $output;
	}

}
```

###  `QRCode` public methods
method | return 
------ | ------
`__construct($data, QROutputInterface $output, QROptions $options = null)` | -
`setData($data, QROptions $options = null)` | `$this` 
`output()` | mixed `QROutputInterface::dump()` 
`getRawData()` | array `QRCode::$matrix` 


###  Properties of `QROptions`

property | type | default | allowed | description
-------- | ---- | ------- | ------- | -----------
`$errorCorrectLevel` | int | M | QRCode::ERROR_CORRECT_LEVEL_X | X = L, M, Q, H<br>7%, 15%, 25%, 30%
`$typeNumber` | int | null | QRCode::TYPE_XX | XX = 01 ... 10, null = auto


###  Properties of `QRStringOptions`

property | type | default | allowed | description
-------- | ---- | ------- | ------- | -----------
`$type` | int | JSON | QRCode::OUTPUT_STRING_XXXX | XXXX = TEXT, JSON
`$textDark` | string | '#' | * | string substitute for dark
`$textLight` | string | ' ' | * | string substitute for light
`$eol` | string | `PHP_EOL` | * | newline string

###  Properties of `QRMarkupOptions`

property | type | default | allowed | description
-------- | ---- | ------- | ------- | -----------
`$type` | int | HTML | QRCode::OUTPUT_MARKUP_XXXX | XXXX = HTML, SVG
`$htmlRowTag` | string | 'p' | * | the shortest available semanically correct row (block) tag to not bloat the output
`$htmlOmitEndTag` | bool | true | - | the closing tag may be omitted (moar bloat!)
`$fgColor` | string | '#000' | * | foreground color
`$bgColor` | string | '#fff' | * | background color
`$cssClass` | string | '' | * | a common css class
`$pixelSize` | int | 5 | * | size of a QR code pixel
`$marginSize` | int | 5 | * | margin around the QR code 
`$eol` | string | `PHP_EOL` | * | newline string

###  Properties of `QRImageOptions`

property | type | default | allowed | description
-------- | ---- | ------- | ------- | -----------
`$type` | string | PNG | QRCode::OUTPUT_IMAGE_XXX | XXX = PNG, JPG, GIF, SVG
`$base64` | bool | true | - | wether to return the image data as base64 or raw like from `file_get_contents()`
`$cachefile` | string | null | * | optional cache file path, null returns the image data
`$pixelSize` | int | 5 | 1 ... 25 | size of a QR code pixel (25 is HUGE!)
`$marginSize` | int | 5 | 0 ... 25 | margin around the QR code 
`$transparent` | bool | true | - | toggle transparency (no jpeg support)
`$fgRed` | int | 0 | 0 ... 255 | foreground red
`$fgGreen` | int | 0 | 0 ... 255 | foreground green
`$fgBlue` | int | 0 | 0 ... 255 | foreground blue
`$bgRed` | int | 255 | 0 ... 255 | background red
`$bgGreen` | int | 255 | 0 ... 255 | background green
`$bgBlue` | int | 255 | 0 ... 255 | background blue
`$pngCompression` | int | -1 | -1 ... 9 | `imagepng()` compression level, -1 = auto
`$jpegQuality` | int | 85 | 0 - 100 | `imagejpeg()` quality
