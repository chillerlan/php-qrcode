# chillerlan/php-qrcode

A PHP7 QR Code library based on the [implementation](https://github.com/kazuhikoarase/qrcode-generator) by [Kazuhiko Arase](https://github.com/kazuhikoarase),
namespaced, cleaned up, improved and other stuff.

[![Packagist version][packagist-badge]][packagist]
[![License][license-badge]][license]
[![Travis CI][travis-badge]][travis]
[![CodeCov][coverage-badge]][coverage]
[![Scrunitizer CI][scrutinizer-badge]][scrutinizer]
[![Gemnasium][gemnasium-badge]][gemnasium]
[![Packagist downloads][downloads-badge]][downloads]
[![PayPal donate][donate-badge]][donate]

[packagist-badge]: https://img.shields.io/packagist/v/chillerlan/php-qrcode.svg?style=flat-square
[packagist]: https://packagist.org/packages/chillerlan/php-qrcode
[license-badge]: https://img.shields.io/github/license/chillerlan/php-qrcode.svg?style=flat-square
[license]: https://github.com/chillerlan/php-qrcode/blob/master/LICENSE
[travis-badge]: https://img.shields.io/travis/chillerlan/php-qrcode.svg?style=flat-square
[travis]: https://travis-ci.org/chillerlan/php-qrcode
[coverage-badge]: https://img.shields.io/codecov/c/github/chillerlan/php-qrcode.svg?style=flat-square
[coverage]: https://codecov.io/github/chillerlan/php-qrcode
[scrutinizer-badge]: https://img.shields.io/scrutinizer/g/chillerlan/php-qrcode.svg?style=flat-square
[scrutinizer]: https://scrutinizer-ci.com/g/chillerlan/php-qrcode
[gemnasium-badge]: https://img.shields.io/gemnasium/chillerlan/php-qrcode.svg?style=flat-square
[gemnasium]: https://gemnasium.com/github.com/chillerlan/php-qrcode
[downloads-badge]: https://img.shields.io/packagist/dt/chillerlan/php-qrcode.svg?style=flat-square
[downloads]: https://packagist.org/packages/chillerlan/php-qrcode/stats
[donate-badge]: https://img.shields.io/badge/donate-paypal-ff33aa.svg?style=flat-square
[donate]: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WLYUNAT9ZTJZ4

## Documentation

### Installation
**requires [composer](https://getcomposer.org)**

*composer.json* (note: replace `dev-master` with a version boundary)
```json
{
	"require": {
		"php": ">=7.0.3",
		"chillerlan/php-qrcode": "dev-master"
	}
}
```

#### Manual installation
Download the desired version of the package from [master](https://github.com/chillerlan/php-qrcode/archive/master.zip) or
[release](https://github.com/chillerlan/php-qrcode/releases) and extract the contents to your project folder.  After that:
- run `composer install` to install the required dependencies and generate `/vendor/autoload.php`.
- if you use a custom autoloader, point the namespace `chillerlan\QRCode` to the folder `src` of the package

Profit!

#### Framework Integration
- Drupal
  - [Google Authenticator Login `ga_login`](https://www.drupal.org/project/ga_login)
- WordPress
  - [Simple 2FA `simple-2fa`](https://wordpress.org/plugins/simple-2fa/)

#### PHP 5
I've dropped PHP 5 support in early 2017 already. PHP 5.6 and 7.0 will be retired in the end of 2018, so there's no reason to stay on these versions and you really should upgrade your server.
However, if upgrading is not an option for you, you can use the unsupported PHP 5.6 backport of the 2.0 branch. It's available as [`1.0.8` on Packagist](https://packagist.org/packages/chillerlan/php-qrcode#1.0.8). Please let PHP 5 die.

### Usage
We want to encode this data into a QRcode image:
```php
// 10 reasons why QR codes are awesome
$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';

// no, for serious, we want to display a QR code for a mobile authenticator
$data = 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net';
```

Quick and simple:
```php
echo '<img src="'.(new QRCode)->render($data).'" />';
```

<p align="center">
	<img alt="QR codes are awesome!" src="https://raw.githubusercontent.com/chillerlan/php-qrcode/master/examples/example_image.png">
	<img alt="QR codes are awesome!" src="https://raw.githubusercontent.com/chillerlan/php-qrcode/master/examples/example_svg.png">
</p>

Wait, what was that? Please again, slower!

### Advanced usage

Ok, step by step. First you'll need a `QRCode` instance, which can be optionally invoked with a `QROptions` (or a [`ContainerInterface`](https://github.com/chillerlan/php-traits/blob/master/src/ContainerInterface.php), respectively) object as the only parameter.

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
```

Once created, you can reuse the `QRCode` object any time:

```php
// set new options if needed
$qrcode->setOptions($newOptions);

// render again
$qrcode->render($newData);
```

In case you just want the raw QR code matrix, call `QRCode::getMatrix()` - this method is also called internally from `QRCode::render()`. See also [Custom output modules](#custom-qroutputinterface).

```php
$matrix = $qrcode->getMatrix($data);

foreach($matrix->matrix() as $y => $row){
	foreach($row as $x => $module){

		// get a module's value
		$value = $module;
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

Have a look [in this folder](https://github.com/chillerlan/php-qrcode/tree/master/examples) for some more usage examples.

#### Custom module values
Previous versions of `QRCode` held only boolean matrix values that only allowed to determine whether a module was dark or not. Now you can distinguish between different parts of the matrix, namely the several required patterns from the QR Code specification, and use them in different ways.

The dark value is the module (light) value shifted by 8 bits to the left: `$value = $M_TYPE << ($bool ? 8 : 0);`, where `$M_TYPE` is one of the `QRMatrix::M_*` constants.
You can check the value for a type explicitly like...
```php
// for true (dark)
$value >> 8 === $M_TYPE;

//for false (light)
$value === $M_TYPE;
```
...or you can perform a loose check, ignoring the module value
```php
// for true
$value >> 8 > 0;

// for false
$value >> 8 === 0
```

See also `QRMatrix::set()`, `QRMatrix::check()` and [`QRMatrix` constants](#qrmatrix-constants).

To map the values and properly render the modules for the given `QROutputInterface`, it's necessary to overwrite the default values:
```php
$options = new QROptions;

// for HTML and SVG
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

Combined with a custom output interface and your imagination you can create some cool effects that way!

#### Custom `QROutputInterface`
Instead of bloating your code you can simply create your own output interface by extending `QROutputAbstract`. Have a look at the [built-in output modules](https://github.com/chillerlan/php-qrcode/tree/master/src/Output).

```php
class MyCustomOutput extends QROutputAbstract{

	// inherited from QROutputAbstract
	protected $matrix;      // QRMatrix
	protected $moduleCount; // length/width of the matrix
	protected $options;     // MyCustomOptions or QROptions

	// optional constructor
	public function __construct(MyCustomOptions $options = null){
		$this->options = $options;

		if(!$this->options instanceof MyCustomOptions){
			// MyCustomOptions should supply default values
			$this->options = new MyCustomOptions;
		}

	}

	// QROutputInterface::dump()
	public function dump(){
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

In case you need additional settings for your output module, just extend `QROptions`...
```
class MyCustomOptions extends QROptions{
	protected $myParam = 'defaultValue';

	// ...
}
```
...or use the [`ContainerInterface`](https://github.com/chillerlan/php-traits/blob/master/src/ContainerInterface.php), which is the more flexible approach.

```php
trait MyCustomOptionsTrait{
	protected $myParam = 'defaultValue';

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

// using the ContainerInterface
$myCustomOptions = new class($myOptions) extends ContainerAbstract{
	use QROptions, MyCustomOptionsTrait;
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

#### Authenticator trait
This library includes a trait for [chillerlan/php-authenticator](https://github.com/chillerlan/php-authenticator) that allows
to create `otpauth://` QR Codes for use with mobile authenticators - just add `"chillerlan/php-authenticator": "^2.0"` to the `require` section of your *composer.json*
```php
use chillerlan\QRCode\{QRCode, QROptions, Traits\QRAuthenticator};

class MyAuthenticatorClass{
	use QRAuthenticator;

	public function getQRCode(){

		// data fetched from wherever
		$this->authenticatorSecret = 'SECRETTEST234567';
		$label = 'my label';
		$issuer = 'example.com';

		// set QROptions options if needed
		$this->qrOptions = new QROptions(['outputType' => QRCode::OUTPUT_MARKUP_SVG]);

		return $this->getURIQRCode($label, $issuer);
	}

}
```

### API

####  `QRCode` methods
method | return | description
------ | ------ | -----------
`__construct(QROptions $options = null)` | - | see [`ContainerInterface`](https://github.com/chillerlan/php-traits/blob/master/src/ContainerInterface.php)
`setOptions(QROptions $options)` | `QRCode` | sets the options, called internally by the constructor
`render(string $data)` | mixed, `QROutputInterface::dump()` | renders a QR Code for the given `$data` and `QROptions`
`getMatrix(string $data)` | `QRMatrix` | returns a `QRMatrix` object for the given `$data` and current `QROptions`
`initDataInterface(string $data)` | `QRDataInterface` | returns a fresh `QRDataInterface` for the given `$data`
`isNumber(string $string)` | bool | checks if a string qualifies for `Number`
`isAlphaNum(string $string)` | bool | checks if a string qualifies for `AlphaNum`
`isKanji(string $string)` | bool | checks if a string qualifies for `Kanji`

####  `QRCode` constants
name | description
---- | -----------
`VERSION_AUTO` | `QROptions::$version`
`MASK_PATTERN_AUTO` | `QROptions::$maskPattern`
`OUTPUT_MARKUP_SVG`, `OUTPUT_MARKUP_HTML` | `QROptions::$outputType` markup
`OUTPUT_IMAGE_PNG`, `OUTPUT_IMAGE_JPG`, `OUTPUT_IMAGE_GIF` | `QROptions::$outputType` image
`OUTPUT_STRING_JSON`, `OUTPUT_STRING_TEXT` | `QROptions::$outputType` string
`OUTPUT_CUSTOM` | `QROptions::$outputType`, requires `QROptions::$outputInterface`
`ECC_L`, `ECC_M`, `ECC_Q`, `ECC_H`, | ECC-Level: 7%, 15%, 25%, 30%  in `QROptions::$eccLevel`
`DATA_NUMBER`, `DATA_ALPHANUM`, `DATA_BYTE`, `DATA_KANJI` | `QRDataInterface::$datamode`

#### `QROptions` properties
property | type | default | allowed | description
-------- | ---- | ------- | ------- | -----------
`$version` | int | `QRCode::VERSION_AUTO` | 1...40 | the [QR Code version number](http://www.qrcode.com/en/about/version.html)
`$versionMin` | int | 1 | 1...40 | Minimum QR version (if `$version = QRCode::VERSION_AUTO`)
`$versionMax` | int | 40 | 1...40 | Maximum QR version (if `$version = QRCode::VERSION_AUTO`)
`$eccLevel` | int | `QRCode::ECC_L` | `QRCode::ECC_X` | Error correct level, where X = L (7%), M (15%), Q (25%), H (30%)
`$maskPattern` | int | `QRCode::MASK_PATTERN_AUTO` | 0...7 | Mask Pattern to use
`$addQuietzone` | bool | true | - | Add a "quiet zone" (margin) according to the QR code spec
`$quietzoneSize` | int | 4 | clamped to 0 ... `$matrixSize / 2` | Size of the quiet zone
`$outputType` | string | `QRCode::OUTPUT_IMAGE_PNG` | `QRCode::OUTPUT_*` | built-in output type
`$outputInterface` | string | null | * | FQCN of the custom `QROutputInterface` if `QROptions::$outputType` is set to `QRCode::OUTPUT_CUSTOM`
`$cachefile` | string | null | * | optional cache file path
`$eol` | string | `PHP_EOL` | * | newline string (HTML, SVG, TEXT)
`$scale` | int | 5 | * | size of a QR code pixel (SVG, IMAGE_*), HTML -> via CSS
`$cssClass` | string | null | * | a common css class
`$textDark` | string | '🔴' | * | string substitute for dark
`$textLight` | string | '⭕' | * | string substitute for light
`$imageBase64` | bool | true | - | whether to return the image data as base64 or raw like from `file_get_contents()`
`$imageTransparent` | bool | true | - | toggle transparency (no jpeg support)
`$imageTransparencyBG` | array | `[255, 255, 255]` | `[R, G, B]` | the RGB values for the transparent color, see [`imagecolortransparent()`](http://php.net/manual/function.imagecolortransparent.php)
`$pngCompression` | int | -1 | -1 ... 9 | `imagepng()` compression level, -1 = auto
`$jpegQuality` | int | 85 | 0 - 100 | `imagejpeg()` quality
`$moduleValues` | array | array | array | Module values map, see [Custom output modules](#custom-qroutputinterface)

#### `QRAuthenticator` trait methods
method | return | description
------ | ------ | -----------
`getURIQRCode(string $label, string $issuer)` | `QRCode::render()` | protected
`getAuthenticator()` | `Authenticator` | protected, returns an `Authenticator` object with the given settings

#### `QRAuthenticator` trait properties
property | type | default | allowed | description
-------- | ---- | ------- | ------- | -----------
`$qrOptions` | `QROptions` | - | - | a `QROptions` object for internal use
`$authenticatorSecret` | string | - | * | the secret phrase to use for the QR Code
`$authenticatorDigits` | int | 6 | 6...8 |
`$authenticatorPeriod` | int | 30 | 10...60 |
`$authenticatorMode` | string | `totp` | `totp`, `hotp` |
`$authenticatorAlgo` | string | `SHA1` | `SHA1`, `SHA256`, `SHA512` |

#### `QRMatrix` methods
method | return | description
------ | ------ | -----------
`__construct(int $version, int $eclevel)` | - | -
`matrix()` | array | the internal matrix representation as a 2 dimensional array
`version()` | int | the current QR Code version
`eccLevel()` | int | current ECC level
`maskPattern()` | int | the used mask pattern
`size()` | int | the absoulute size of the matrix, including quiet zone (if set). `$version * 4 + 17 + 2 * $quietzone`
`get(int $x, int $y)` | int | returns the value of the module
`set(int $x, int $y, bool $value, int $M_TYPE)` | `QRMatrix` | sets the `$M_TYPE` value for the module
`check(int $x, int $y)` | bool | checks whether a module is true (dark) or false (light)

#### `QRMatrix` constants
name | light (false) | dark (true) | description
---- | ------------- | ----------- | -----------
`M_NULL` | 0 | - | module not set (should never appear. if so, there's an error)
`M_DARKMODULE` | - (2) | 512 | once per matrix at `$xy = [8, 4 * $version + 9]`
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


### Notes
The QR encoder, especially the subroutines for mask pattern testing, can cause high CPU load on increased matrix size.
You can avoid a part of this load by choosing a fast output module, like `OUTPUT_IMAGE_*` and setting the mask pattern manually (which may result in unreadable QR Codes).
Oh hey and don't forget to sanitize any user input!

### Disclaimer!
I don't take responsibility for molten CPUs, misled applications, failed log-ins etc.. Use at your own risk!
