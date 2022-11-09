# chillerlan/php-qrcode

A PHP QR Code library based on the [implementation by Kazuhiko Arase](https://github.com/kazuhikoarase/qrcode-generator),
namespaced, cleaned up, improved and other stuff. It also features a QR Code reader which is based on the [ZXing library](https://github.com/zxing/zxing).

**Attention:** there is now also a javascript port: [chillerlan/js-qrcode](https://github.com/chillerlan/js-qrcode).

[![PHP Version Support][php-badge]][php]
[![Packagist version][packagist-badge]][packagist]
[![License][license-badge]][license]
[![Continuous Integration][gh-action-badge]][gh-action]
[![CodeCov][coverage-badge]][coverage]
[![Scrunitizer CI][scrutinizer-badge]][scrutinizer]
[![Packagist downloads][downloads-badge]][downloads]

[php-badge]: https://img.shields.io/packagist/php-v/chillerlan/php-qrcode?logo=php&color=8892BF
[php]: https://www.php.net/supported-versions.php
[packagist-badge]: https://img.shields.io/packagist/v/chillerlan/php-qrcode.svg?logo=packagist
[packagist]: https://packagist.org/packages/chillerlan/php-qrcode
[license-badge]: https://img.shields.io/badge/license-MIT-green.svg
[license]: https://github.com/chillerlan/php-qrcode/blob/main/LICENSE-MIT
[coverage-badge]: https://img.shields.io/codecov/c/github/chillerlan/php-qrcode.svg?logo=codecov
[coverage]: https://codecov.io/github/chillerlan/php-qrcode
[scrutinizer-badge]: https://img.shields.io/scrutinizer/g/chillerlan/php-qrcode.svg?logo=scrutinizer
[scrutinizer]: https://scrutinizer-ci.com/g/chillerlan/php-qrcode
[downloads-badge]: https://img.shields.io/packagist/dt/chillerlan/php-qrcode.svg?logo=packagist
[downloads]: https://packagist.org/packages/chillerlan/php-qrcode/stats
[gh-action-badge]: https://img.shields.io/github/workflow/status/chillerlan/php-qrcode/Continuous%20Integration?logo=github
[gh-action]: https://github.com/chillerlan/php-qrcode/actions/workflows/tests.yml?query=branch%3Amain

## Features

- Generation of [Model 2 QR Codes](https://www.qrcode.com/en/codes/model12.html), [Version 1 to 40](https://www.qrcode.com/en/about/version.html)
- [ECC Levels](https://www.qrcode.com/en/about/error_correction.html) L/M/Q/H supported
- Mixed mode support (encoding modes can be mixed as needed within a QR symbol)
- Flexible, easily extensible output modules
- Built-in support for the following output formats:
  - [GdImage](https://www.php.net/manual/book.image)
  - [ImageMagick](https://www.php.net/manual/book.imagick)
  - Markup types: SVG, HTML, etc.
  - String types: JSON, plain text, etc.
  - Encapsulated Postscript (EPS)
  - PDF via [FPDF](https://github.com/setasign/fpdf)
- QR Code reader (via GD and ImageMagick)

## Documentation

See [the wiki](https://github.com/chillerlan/php-qrcode/wiki) for advanced documentation.
An API documentation created with [phpDocumentor](https://www.phpdoc.org/) can be found at https://chillerlan.github.io/php-qrcode/ (WIP).

### Requirements
- PHP 7.4+
  - [`ext-mbstring`](https://www.php.net/manual/book.mbstring.php)
  - optional:
    - [`ext-gd`](https://www.php.net/manual/book.image)
    - [`ext-imagick`](https://github.com/Imagick/imagick) with [ImageMagick](https://imagemagick.org) installed
    - [`setasign/fpdf`](https://github.com/setasign/fpdf) for the PDF output module

For the QRCode reader, either `ext-gd` or `ext-imagick` is required!

### Installation
**requires [composer](https://getcomposer.org)**

via terminal:
```
composer require chillerlan/php-qrcode
```
via `composer.json`:
```json
{
	"require": {
		"php": "^7.4 || ^8.0",
		"chillerlan/php-qrcode": "dev-main"
	}
}
```
Note: replace `dev-main` with a [version constraint](https://getcomposer.org/doc/articles/versions.md#writing-version-constraints), e.g. `^4.3` - see [releases](https://github.com/chillerlan/php-qrcode/releases) for valid versions.
See [the installation guide on the wiki](https://github.com/chillerlan/php-qrcode/wiki/Installation) for more info!

### Quickstart
We want to encode this URI for a mobile authenticator into a QRcode image:
```php
$data = 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net';

// quick and simple:
echo '<img src="'.(new QRCode)->render($data).'" alt="QR Code" />';
```
<p align="center">
	<img alt="QR codes are awesome!" style="width: auto; height: 530px;" src="https://raw.githubusercontent.com/chillerlan/php-qrcode/main/.github/images/example.svg">
</p>

Wait, what was that? Please again, slower! See [Advanced usage](https://github.com/chillerlan/php-qrcode/wiki/Advanced-usage) on the wiki.
Also, have a look [in the examples folder](https://github.com/chillerlan/php-qrcode/tree/main/examples) for some more usage examples.

### Reading QR Codes

Using the built-in QR Code reader is pretty straight-forward:
```php
$result = (new QRCode)->readFromFile('path/to/file.png'); // -> DecoderResult

// you can now use the result instance...
$content = $result->data;
$matrix  = $result->getMatrix(); // -> QRMatrix

// ...or simply cast it to string to get the content:
$content = (string)$result;
```
It's generally a good idea to wrap the reading in a try/catch block to handle any errors that may occur in the process.

### Framework Integration
- Drupal:
  - [Google Authenticator Login `ga_login`](https://www.drupal.org/project/ga_login)
- Symfony
  - [phpqrcode-bundle](https://github.com/jonasarts/phpqrcode-bundle)
- WordPress:
  - [wp-two-factor-auth](https://github.com/sjinks/wp-two-factor-auth)
  - [simple-2fa](https://wordpress.org/plugins/simple-2fa/)
  - [wordpress-seo](https://github.com/Yoast/wordpress-seo)
  - [floating-share-button](https://github.com/qriouslad/floating-share-button)
- WoltLab Suite
  - [two-step-verification](http://pluginstore.woltlab.com/file/3007-two-step-verification/)
- other uses:
  - [dependents](https://github.com/chillerlan/php-qrcode/network/dependents) / [packages](https://github.com/chillerlan/php-qrcode/network/dependents?dependent_type=PACKAGE)
  - [Appwrite](https://github.com/appwrite/appwrite)
  - [Cachet](https://github.com/CachetHQ/Cachet)
  - [GÉANT CAT](https://github.com/GEANT/CAT)
  - [openITCOCKPIT](https://github.com/it-novum/openITCOCKPIT)
  - [twill](https://github.com/area17/twill)

### Shameless advertising
Hi, please check out my other projects that are way cooler than qrcodes!

- [php-oauth-core](https://github.com/chillerlan/php-oauth-core) - an OAuth 1/2 client library along with a bunch of [providers](https://github.com/chillerlan/php-oauth-providers)
- [php-httpinterface](https://github.com/chillerlan/php-httpinterface) - a PSR-7/15/17/18 implemetation
- [php-database](https://github.com/chillerlan/php-database) - a database client & querybuilder for MySQL, Postgres, SQLite, MSSQL, Firebird

### Disclaimer!
I don't take responsibility for molten CPUs, misled applications, failed log-ins etc.. Use at your own risk!

#### License notice
Parts of this code are [ported to php](https://github.com/khanamiryan/php-qrcode-detector-decoder) from the [ZXing project](https://github.com/zxing/zxing) and licensed under the [Apache License, Version 2.0](./NOTICE).

#### Trademark Notice

The word "QR Code" is a registered trademark of *DENSO WAVE INCORPORATED*<br>
https://www.qrcode.com/en/faq.html#patentH2Title
