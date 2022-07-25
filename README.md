# chillerlan/php-qrcode

A PHP 7.4+ QR Code library based on the [implementation](https://github.com/kazuhikoarase/qrcode-generator) by [Kazuhiko Arase](https://github.com/kazuhikoarase),
namespaced, cleaned up, improved and other stuff.

**Attention:** there is now also a javascript port: [chillerlan/js-qrcode](https://github.com/chillerlan/js-qrcode).

[![PHP Version Support][php-badge]][php]
[![Packagist version][packagist-badge]][packagist]
[![License][license-badge]][license]
[![CodeCov][coverage-badge]][coverage]
[![Scrunitizer CI][scrutinizer-badge]][scrutinizer]
[![Packagist downloads][downloads-badge]][downloads]<br/>
[![Continuous Integration][gh-action-badge]][gh-action]

[php-badge]: https://img.shields.io/packagist/php-v/chillerlan/php-qrcode?logo=php&color=8892BF
[php]: https://www.php.net/supported-versions.php
[packagist-badge]: https://img.shields.io/packagist/v/chillerlan/php-qrcode.svg?logo=packagist
[packagist]: https://packagist.org/packages/chillerlan/php-qrcode
[license-badge]: https://img.shields.io/github/license/chillerlan/php-qrcode.svg
[license]: https://github.com/chillerlan/php-qrcode/blob/main/LICENSE
[coverage-badge]: https://img.shields.io/codecov/c/github/chillerlan/php-qrcode.svg?logo=codecov
[coverage]: https://codecov.io/github/chillerlan/php-qrcode
[scrutinizer-badge]: https://img.shields.io/scrutinizer/g/chillerlan/php-qrcode.svg?logo=scrutinizer
[scrutinizer]: https://scrutinizer-ci.com/g/chillerlan/php-qrcode
[downloads-badge]: https://img.shields.io/packagist/dt/chillerlan/php-qrcode.svg?logo=packagist
[downloads]: https://packagist.org/packages/chillerlan/php-qrcode/stats
[gh-action-badge]: https://github.com/chillerlan/php-qrcode/workflows/Continuous%20Integration/badge.svg
[gh-action]: https://github.com/chillerlan/php-qrcode/actions?query=workflow%3A%22Continuous+Integration%22+branch%3Av4.3.x

# Documentation

## Requirements
- PHP 7.4+
  - `ext-mbstring`
  - optional:
    - `ext-json`, `ext-gd`
    - `ext-imagick` with [ImageMagick](https://imagemagick.org) installed
    - [`setasign/fpdf`](https://github.com/setasign/fpdf) for the PDF output module

## Installation
**requires [composer](https://getcomposer.org)**

via terminal: `composer require chillerlan/php-qrcode`

*composer.json*
```json
{
	"require": {
		"php": "^7.4 || ^8.0",
		"chillerlan/php-qrcode": "v4.3.x-dev"
	}
}
```

Note: replace `v4.3.x-dev` with a [version constraint](https://getcomposer.org/doc/articles/versions.md#writing-version-constraints), e.g. `^4.3` - see [releases](https://github.com/chillerlan/php-qrcode/releases) for valid versions.
For PHP version ...
  - 7.4+ use `^4.3`
  - 7.2+ use `^3.4.1` (v3.4.1 also supports PHP8)
  - 7.0+ use `^2.0`
  - 5.6+ use `^1.0` (please let PHP 5 die!)

In case you want to keep using `v4.3.x-dev`, specify the hash of a commit to avoid running into unforseen issues like so: `v4.3.x-dev#c115f7bc51d466ccb24c544e88329804aad8c2a0`

PSA: [PHP 7.0 - 7.3 are EOL](https://www.php.net/supported-versions.php) and therefore the respective `QRCode` versions are also no longer supported!

## Quickstart
We want to encode this URI for a mobile authenticator into a QRcode image:
```php
$data = 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net';

// quick and simple:
echo '<img src="'.(new QRCode)->render($data).'" alt="QR Code" />';
```

<p align="center">
	<img alt="QR codes are awesome!" src="https://raw.githubusercontent.com/chillerlan/php-qrcode/v4.3.x/examples/example_image.png">
	<img alt="QR codes are awesome!" src="https://raw.githubusercontent.com/chillerlan/php-qrcode/v4.3.x/examples/example_svg.png">
</p>

Wait, what was that? Please again, slower!


## Advanced usage

Ok, step by step. First you'll need a `QRCode` instance, which can be optionally invoked with a `QROptions` (or a [`SettingsContainerInterface`](https://github.com/chillerlan/php-settings-container/blob/master/src/SettingsContainerInterface.php), respectively) object as the only parameter.

```php
$options = new QROptions([
	'version'    => 5,
	'outputType' => QRCode::OUTPUT_MARKUP_SVG,
	'eccLevel'   => QRCode::ECC_L,
]);

// invoke a fresh QRCode instance
$qrcode = new QRCode($options);

// and dump the output
$qrcode->render($data);

// ...with additional cache file
$qrcode->render($data, '/path/to/file.svg');
```

In case you just want the raw QR code matrix, call `QRCode::getMatrix()` - this method is also called internally from `QRCode::render()`. See also [[Custom output interface]].

```php
$matrix = $qrcode->getMatrix($data);

foreach($matrix->matrix() as $y => $row){
	foreach($row as $x => $module){

		// get a module's value
		$value = $module;

		// or via the matrix's getter method
		$value = $matrix->get($x, $y);

		// boolean check a module
		if($matrix->check($x, $y)){ // if($module >> 8 > 0)
			// do stuff, the module is dark
		}
		else{
			// do other stuff, the module is light
		}

	}
}
```

Have a look [in the examples folder](https://github.com/chillerlan/php-qrcode/tree/main/examples) for some more usage examples.

### Notes
The QR encoder, especially the subroutines for mask pattern testing, can cause high CPU load on increased matrix size.
You can avoid a part of this load by choosing a fast output module, like `OUTPUT_IMAGE_*` and maybe setting the mask pattern manually (which may result in unreadable QR Codes).
Oh hey and don't forget to sanitize any user input!


## Custom output interface

Instead of bloating your code you can simply create your own output interface by creating a `QROutputInterface` (i.e. extending `QROutputAbstract`).

```php
class MyCustomOutput extends QROutputAbstract{

	// inherited from QROutputAbstract
	protected QRMatrix $matrix;   // QRMatrix
	protected int $moduleCount;   // modules QRMatrix::size()
	protected QROptions $options; // MyCustomOptions or QROptions
	protected int $scale;         // scale factor from options
	protected int $length;        // length of the matrix ($moduleCount * $scale)

	// ...check/set default module values (abstract method, called by the constructor)
	protected function setModuleValues():void{
		// $this->moduleValues = ...
	}

	// QROutputInterface::dump()
	public function dump(string $file = null):string{
		$output = '';

		for($row = 0; $row < $this->moduleCount; $row++){
			for($col = 0; $col < $this->moduleCount; $col++){
				$output .= (int)$this->matrix->check($col, $row);
			}
		}

		return $output;
	}

}
```
For more examples, have a look at the [built-in output modules](https://github.com/chillerlan/php-qrcode/tree/main/src/Output).

In case you need additional settings for your output module, just extend `QROptions`...
```
class MyCustomOptions extends QROptions{
	protected string $myParam = 'defaultValue';

	// ...
}
```
...or use the [`SettingsContainerInterface`](https://github.com/chillerlan/php-settings-container/blob/main/src/SettingsContainerInterface.php), which is the more flexible approach.

```php
trait MyCustomOptionsTrait{
	protected string $myParam = 'defaultValue';

	// ...
}
```
set the options:
```php
$myOptions = [
	'version'         => 5,
	'eccLevel'        => QRCode::ECC_L,
	'outputType'      => QRCode::OUTPUT_CUSTOM,
	'outputInterface' => MyCustomOutput::class,
	// your custom settings
	'myParam'         => 'whatever value',
 ];

// extends QROptions
$myCustomOptions = new MyCustomOptions($myOptions);

// using the SettingsContainerInterface
$myCustomOptions = new class($myOptions) extends SettingsContainerAbstract{
	use QROptionsTrait, MyCustomOptionsTrait;
};

```

You can then call `QRCode` with the custom modules...
```php
(new QRCode($myCustomOptions))->render($data);
```
...or invoke the `QROutputInterface` manually.
```php
$qrOutputInterface = new MyCustomOutput($myCustomOptions, (new QRCode($myCustomOptions))->getMatrix($data));

//dump the output, which is equivalent to QRCode::render()
$qrOutputInterface->dump();
```

### Custom module values

You can distinguish between different parts of the matrix, namely the several required patterns from the QR Code specification, and use them in different ways, i.e. to assign different colors for each part of the matrix (see the [image example](https://github.com/chillerlan/php-qrcode/blob/main/examples/image.php)).
The dark value is the module value (light) shifted by 8 bits to the left: `$value = $M_TYPE << ($bool ? 8 : 0);`, where `$M_TYPE` is one of the `QRMatrix::M_*` constants.
You can check the value for a type explicitly like...
```php
// for true (dark)
($value >> 8) === $M_TYPE;

// for false (light)
$value === $M_TYPE;
```
...or you can perform a loose check, ignoring the module value
```php
// for true
($value >> 8) > 0;

// for false
($value >> 8) === 0;
```

See also `QRMatrix::set()`, `QRMatrix::check()` and [`QRMatrix` constants](#qrmatrix-constants).

To map the values and properly render the modules for the given `QROutputInterface`, it's necessary to overwrite the default values:
```php
$options = new QROptions;

// for HTML, SVG and ImageMagick
$options->moduleValues = [
	// finder
	1536 => '#A71111', // dark (true)
	6    => '#FFBFBF', // light (false)
	// alignment
	2560 => '#A70364',
	10   => '#FFC9C9',
	// timing
	3072 => '#98005D',
	12   => '#FFB8E9',
	// format
	3584 => '#003804',
	14   => '#00FB12',
	// version
	4096 => '#650098',
	16   => '#E0B8FF',
	// data
	1024 => '#4A6000',
	4    => '#ECF9BE',
	// darkmodule
	512  => '#080063',
	// separator
	8    => '#AFBFBF',
	// quietzone
	18   => '#FFFFFF',
];

// for the image output types
$options->moduleValues = [
	512  => [0, 0, 0],
	// ...
];

// for string/text output
$options->moduleValues = [
	512  => '#',
	// ...
];
```


## Public API

### `QRCode` API
####  Methods
method | return | description
------ | ------ | -----------
`__construct(QROptions $options = null)` | - | see [`SettingsContainerInterface`](https://github.com/chillerlan/php-settings-container/blob/main/src/SettingsContainerInterface.php)
`render(string $data, string $file = null)` | mixed, `QROutputInterface::dump()` | renders a QR Code for the given `$data` and `QROptions`, saves `$file` optional
`getMatrix(string $data)` | `QRMatrix` | returns a `QRMatrix` object for the given `$data` and current `QROptions`
`initDataInterface(string $data)` | `QRDataInterface` | returns a fresh `QRDataInterface` for the given `$data`
`isNumber(string $string)` | bool | checks if a string qualifies for `Number`
`isAlphaNum(string $string)` | bool | checks if a string qualifies for `AlphaNum`
`isKanji(string $string)` | bool | checks if a string qualifies for `Kanji`
`isByte(string $string)` | bool | checks if a string is non-empty

####  Constants
name | description
---- | -----------
`VERSION_AUTO` | `QROptions::$version`
`MASK_PATTERN_AUTO` | `QROptions::$maskPattern`
`OUTPUT_MARKUP_SVG`, `OUTPUT_MARKUP_HTML` | `QROptions::$outputType` markup
`OUTPUT_IMAGE_PNG`, `OUTPUT_IMAGE_JPG`, `OUTPUT_IMAGE_GIF` | `QROptions::$outputType` image
`OUTPUT_STRING_JSON`, `OUTPUT_STRING_TEXT` | `QROptions::$outputType` string
`OUTPUT_IMAGICK` | `QROptions::$outputType` ImageMagick
`OUTPUT_FPDF` | `QROptions::$outputType` PDF, using [FPDF](https://github.com/setasign/fpdf)
`OUTPUT_CUSTOM` | `QROptions::$outputType`, requires `QROptions::$outputInterface`
`ECC_L`, `ECC_M`, `ECC_Q`, `ECC_H`, | ECC-Level: 7%, 15%, 25%, 30%  in `QROptions::$eccLevel`
`DATA_NUMBER`, `DATA_ALPHANUM`, `DATA_BYTE`, `DATA_KANJI` | `QRDataInterface::$datamode`


### `QRMatrix` API

#### Methods
method | return | description
------ | ------ | -----------
`__construct(int $version, int $eclevel)` | - | -
`init(int $maskPattern, bool $test = null)` | `QRMatrix` |
`matrix()` | array | the internal matrix representation as a 2 dimensional array
`version()` | int | the current QR Code version
`eccLevel()` | int | current ECC level
`maskPattern()` | int | the used mask pattern
`size()` | int | the absoulute size of the matrix, including quiet zone (if set). `$version * 4 + 17 + 2 * $quietzone`
`get(int $x, int $y)` | int | returns the value of the module
`set(int $x, int $y, bool $value, int $M_TYPE)` | `QRMatrix` | sets the `$M_TYPE` value for the module
`check(int $x, int $y)` | bool | checks whether a module is true (dark) or false (light)

#### Constants
name | light (false) | dark (true) | description
---- | ------------- | ----------- | -----------
`M_NULL` | 0 | - | module not set (should never appear. if so, there's an error)
`M_DARKMODULE` | - | 512 | once per matrix at `$xy = [8, 4 * $version + 9]`
`M_DATA` | 4 | 1024 | the actual encoded data
`M_FINDER` | 6 | 1536 | the 7x7 finder patterns
`M_SEPARATOR` | 8 | - | separator lines around the finder patterns
`M_ALIGNMENT` | 10 | 2560 | the 5x5 alignment patterns
`M_TIMING` | 12 | 3072 | the timing pattern lines
`M_FORMAT` | 14 | 3584 | format information pattern
`M_VERSION` | 16 | 4096 | version information pattern
`M_QUIETZONE` | 18 | - | margin around the QR Code
`M_LOGO` | 20 | - | space for a logo image (not used yet)
`M_TEST` | 255 | 65280 | test value

### `QROptions` API

#### Properties
property | type | default | allowed | description
-------- | ---- | ------- | ------- | -----------
`$version` | int | `QRCode::VERSION_AUTO` | 1...40 | the [QR Code version number](http://www.qrcode.com/en/about/version.html)
`$versionMin` | int | 1 | 1...40 | Minimum QR version (if `$version = QRCode::VERSION_AUTO`)
`$versionMax` | int | 40 | 1...40 | Maximum QR version (if `$version = QRCode::VERSION_AUTO`)
`$eccLevel` | int | `QRCode::ECC_L` | `QRCode::ECC_X` | Error correct level, where X = L (7%), M (15%), Q (25%), H (30%)
`$maskPattern` | int | `QRCode::MASK_PATTERN_AUTO` | 0...7 | Mask Pattern to use
`$addQuietzone` | bool | `true` | - | Add a "quiet zone" (margin) according to the QR code spec
`$quietzoneSize` | int | 4 | clamped to 0 ... `$matrixSize / 2` | Size of the quiet zone
`$dataModeOverride` | string | `null` | `Number`, `AlphaNum`, `Kanji`, `Byte` | allows overriding the data type detection
`$outputType` | string | `QRCode::OUTPUT_IMAGE_PNG` | `QRCode::OUTPUT_*` | built-in output type
`$outputInterface` | string | `null` | * | FQCN of the custom `QROutputInterface` if `QROptions::$outputType` is set to `QRCode::OUTPUT_CUSTOM`
`$cachefile` | string | `null` | * | optional cache file path
`$eol` | string | `PHP_EOL` | * | newline string (HTML, SVG, TEXT)
`$scale` | int | 5 | * | size of a QR code pixel (SVG, IMAGE_*), HTML -> via CSS
`$cssClass` | string | `null` | * | a common css class
`$svgOpacity` | float | 1.0 | 0...1 |
`$svgDefs` | string | * | * | anything between [`<defs>`](https://developer.mozilla.org/docs/Web/SVG/Element/defs)
`$svgViewBoxSize` | int | `null` | * | a positive integer which defines width/height of the [viewBox attribute](https://css-tricks.com/scale-svg/#article-header-id-3)
`$textDark` | string | '🔴' | * | string substitute for dark
`$textLight` | string | '⭕' | * | string substitute for light
`$markupDark` | string | '#000' | * | markup substitute for dark (CSS value)
`$markupLight` | string | '#fff' | * | markup substitute for light (CSS value)
`$imageBase64` | bool | `true` | - | whether to return the image data as base64 or raw like from `file_get_contents()`
`$imageTransparent` | bool | `true` | - | toggle transparency (no jpeg support)
`$imageTransparencyBG` | array | `[255, 255, 255]` | `[R, G, B]` | the RGB values for the transparent color, see [`imagecolortransparent()`](http://php.net/manual/function.imagecolortransparent.php)
`$pngCompression` | int | -1 | -1 ... 9 | `imagepng()` compression level, -1 = auto
`$jpegQuality` | int | 85 | 0 - 100 | `imagejpeg()` quality
`$imagickFormat` | string | 'png' | * | ImageMagick output type, see `Imagick::setType()`
`$imagickBG` | string | `null` | * | ImageMagick background color, see `ImagickPixel::__construct()`
`$moduleValues` | array | `null` | * | Module values map, see [[Custom output interface]] and `QROutputInterface::DEFAULT_MODULE_VALUES`


## Framework Integration
- Drupal:
  - [Google Authenticator Login `ga_login`](https://www.drupal.org/project/ga_login)
- Symfony
	- [phpqrcode-bundle](https://github.com/jonasarts/phpqrcode-bundle)
- WordPress:
  - [`wp-two-factor-auth`](https://github.com/sjinks/wp-two-factor-auth)
  - [`simple-2fa`](https://wordpress.org/plugins/simple-2fa/)
  - [`wordpress-seo`](https://github.com/Yoast/wordpress-seo)
  - [`floating-share-button`](https://github.com/qriouslad/floating-share-button)
- WoltLab Suite
  - [two-step-verification](http://pluginstore.woltlab.com/file/3007-two-step-verification/)
- [Appwrite](https://github.com/appwrite/appwrite)
- [Cachet](https://github.com/CachetHQ/Cachet)
- [twill](https://github.com/area17/twill)
- other uses: [dependents](https://github.com/chillerlan/php-qrcode/network/dependents) / [packages](https://github.com/chillerlan/php-qrcode/network/dependents?dependent_type=PACKAGE)

## Shameless advertising
Hi, please check out my other projects that are way cooler than qrcodes!

- [php-oauth-core](https://github.com/chillerlan/php-oauth-core) - an OAuth 1/2 client library along with a bunch of [providers](https://github.com/chillerlan/php-oauth-providers)
- [php-httpinterface](https://github.com/chillerlan/php-httpinterface) - a PSR-7/15/17/18 implemetation
- [php-database](https://github.com/chillerlan/php-database) - a database client & querybuilder for MySQL, Postgres, SQLite, MSSQL, Firebird

## Disclaimer!
I don't take responsibility for molten CPUs, misled applications, failed log-ins etc.. Use at your own risk!

### Trademark Notice

The word "QR Code" is a registered trademark of *DENSO WAVE INCORPORATED*<br>
https://www.qrcode.com/en/faq.html#patentH2Title
