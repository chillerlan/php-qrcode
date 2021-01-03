# chillerlan/php-qrcode

A PHP 7.4+ QR Code library based on the [implementation](https://github.com/kazuhikoarase/qrcode-generator) by [Kazuhiko Arase](https://github.com/kazuhikoarase),
namespaced, cleaned up, improved and other stuff.

[![PHP Version Support][php-badge]][php]
[![Packagist version][packagist-badge]][packagist]
[![License][license-badge]][license]
[![Travis CI][travis-badge]][travis]
[![CodeCov][coverage-badge]][coverage]
[![Scrunitizer CI][scrutinizer-badge]][scrutinizer]
[![Packagist downloads][downloads-badge]][downloads]<br/>
[![Continuous Integration][gh-action-badge]][gh-action] 
[![phpDocs][gh-docs-badge]][gh-docs]

[php-badge]: https://img.shields.io/packagist/php-v/chillerlan/php-qrcode?logo=php&color=8892BF
[php]: https://www.php.net/supported-versions.php
[packagist-badge]: https://img.shields.io/packagist/v/chillerlan/php-qrcode.svg
[packagist]: https://packagist.org/packages/chillerlan/php-qrcode
[license-badge]: https://img.shields.io/github/license/chillerlan/php-qrcode.svg
[license]: https://github.com/chillerlan/php-qrcode/blob/main/LICENSE
[travis-badge]: https://img.shields.io/travis/com/chillerlan/php-qrcode/main?logo=travis
[travis]: https://travis-ci.com/github/chillerlan/php-qrcode
[coverage-badge]: https://img.shields.io/codecov/c/github/chillerlan/php-qrcode.svg?logo=codecov
[coverage]: https://codecov.io/github/chillerlan/php-qrcode
[scrutinizer-badge]: https://img.shields.io/scrutinizer/g/chillerlan/php-qrcode.svg?logo=scrutinizer
[scrutinizer]: https://scrutinizer-ci.com/g/chillerlan/php-qrcode
[downloads-badge]: https://img.shields.io/packagist/dt/chillerlan/php-qrcode.svg
[downloads]: https://packagist.org/packages/chillerlan/php-qrcode/stats
[gh-action-badge]: https://github.com/chillerlan/php-qrcode/workflows/Continuous%20Integration/badge.svg
[gh-action]: https://github.com/chillerlan/php-qrcode/actions?query=workflow%3A%22Continuous+Integration%22
[gh-docs-badge]: https://github.com/chillerlan/php-qrcode/workflows/Docs/badge.svg
[gh-docs]: https://github.com/chillerlan/php-qrcode/actions?query=workflow%3ADocs

## Documentation

See [the wiki](https://github.com/chillerlan/php-qrcode/wiki) for advanced documentation. 
An API documentation created with [phpDocumentor](https://www.phpdoc.org/) can be found at https://chillerlan.github.io/php-qrcode/ (WIP).

### Requirements
- PHP 7.4+
  - `ext-mbstring`
  - optional: 
    - `ext-json`, `ext-gd`
    - `ext-imagick` with [ImageMagick](https://imagemagick.org) installed
    - [`setasign/fpdf`](https://github.com/setasign/fpdf) for the PDF output module

### Installation
**requires [composer](https://getcomposer.org)**

via terminal: `composer require chillerlan/php-qrcode`

*composer.json* 
```json
{
	"require": {
		"php": "^7.4",
		"chillerlan/php-qrcode": "dev-main"
	}
}
```

Note: replace `dev-main` with a [version constraint](https://getcomposer.org/doc/articles/versions.md#writing-version-constraints), e.g. `^3.2` - see [releases](https://github.com/chillerlan/php-qrcode/releases) for valid versions.
For PHP version ... 
  - 7.4+ use `^4.2`
  - 7.2+ use `^3.3`
  - 7.0+ use `^2.0` (PHP 7.0 and 7.1 are EOL!)
  - 5.6+ use `^1.0` (please let PHP 5 die!)

### Quickstart
We want to encode this URI for a mobile authenticator into a QRcode image:
```php
$data = 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net';

// quick and simple:
echo '<img src="'.(new QRCode)->render($data).'" alt="QR Code" />';
```

<p align="center">
	<img alt="QR codes are awesome!" src="https://raw.githubusercontent.com/chillerlan/php-qrcode/main/examples/example_image.png">
	<img alt="QR codes are awesome!" src="https://raw.githubusercontent.com/chillerlan/php-qrcode/main/examples/example_svg.png">
</p>

Wait, what was that? Please again, slower! See [Advanced usage](https://github.com/chillerlan/php-qrcode/wiki/Advanced-usage) on the wiki.

### Framework Integration
- Drupal [Google Authenticator Login `ga_login`](https://www.drupal.org/project/ga_login)
- WordPress [`wp-two-factor-auth`](https://github.com/sjinks/wp-two-factor-auth)
- WordPress [Simple 2FA `simple-2fa`](https://wordpress.org/plugins/simple-2fa/)
- WoltLab Suite [two-step-verification](http://pluginstore.woltlab.com/file/3007-two-step-verification/)  
- [Cachet](https://github.com/CachetHQ/Cachet)
- [Appwrite](https://github.com/appwrite/appwrite)  
- other uses: [dependents](https://github.com/chillerlan/php-qrcode/network/dependents) / [packages](https://github.com/chillerlan/php-qrcode/network/dependents?dependent_type=PACKAGE)

### Shameless advertising
Hi, please check out my other projects that are way cooler than qrcodes!

- [php-oauth-core](https://github.com/chillerlan/php-oauth-core) - an OAuth 1/2 client library along with a bunch of [providers](https://github.com/chillerlan/php-oauth-providers)
- [php-httpinterface](https://github.com/chillerlan/php-httpinterface) - a PSR-7/15/17/18 implemetation
- [php-database](https://github.com/chillerlan/php-database) - a database client & querybuilder for MySQL, Postgres, SQLite, MSSQL, Firebird

### Disclaimer!
I don't take responsibility for molten CPUs, misled applications, failed log-ins etc.. Use at your own risk!

#### Trademark Notice

The word "QR Code" is registered trademark of *DENSO WAVE INCORPORATED*<br>
http://www.denso-wave.com/qrcode/faqpatent-e.html
