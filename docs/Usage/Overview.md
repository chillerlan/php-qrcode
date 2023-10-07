# Overview

A PHP QR Code generator based on the [implementation by Kazuhiko Arase](https://github.com/kazuhikoarase/qrcode-generator), namespaced, cleaned up, improved and other stuff.
It also features a QR Code reader based on a [PHP port](https://github.com/khanamiryan/php-qrcode-detector-decoder) of the [ZXing library](https://github.com/zxing/zxing).


## Features

- Creation of [Model 2 QR Codes](https://www.qrcode.com/en/codes/model12.html), [Version 1 to 40](https://www.qrcode.com/en/about/version.html)
- [ECC Levels](https://www.qrcode.com/en/about/error_correction.html) L/M/Q/H supported
- Mixed mode support (encoding modes can be combined within a QR symbol). Supported modes:
  - numeric
  - alphanumeric
  - 8-bit binary
  - 13-bit double-byte:
    - kanji (Japanese, Shift-JIS)
    - hanzi (simplified Chinese, GB2312/GB18030) as [defined in GBT18284-2000](https://www.chinesestandard.net/PDF/English.aspx/GBT18284-2000)
- Flexible, easily extensible output modules, built-in support for the following output formats:
  - [GdImage](https://www.php.net/manual/book.image)
  - [ImageMagick](https://www.php.net/manual/book.imagick)
  - Markup types: SVG, HTML, etc.
  - String types: JSON, plain text, etc.
  - Encapsulated Postscript (EPS)
  - PDF via [FPDF](https://github.com/setasign/fpdf)
- QR Code reader (via GD and ImageMagick)


## Requirements

- PHP 7.4+
  - [`ext-mbstring`](https://www.php.net/manual/book.mbstring.php)
  - optional:
    - [`ext-fileinfo`](https://www.php.net/manual/book.fileinfo.php) (required by `QRImagick` output)
    - [`ext-gd`](https://www.php.net/manual/book.image)
    - [`ext-imagick`](https://github.com/Imagick/imagick) with [ImageMagick](https://imagemagick.org) installed
    - [`setasign/fpdf`](https://github.com/setasign/fpdf) for the PDF output module

For the QR Code reader, either `ext-gd` or `ext-imagick` is required!


## Framework Integration

- Drupal:
  - [Two-factor Authentication `tfa`](https://www.drupal.org/project/tfa) (Drupal 8+)
  - [Google Authenticator Login `ga_login`](https://www.drupal.org/project/ga_login) (deprecated, Drupal 7)
- Symfony
  - [phpqrcode-bundle](https://github.com/jonasarts/phpqrcode-bundle)
- WordPress:
  - [wp-two-factor-auth](https://github.com/sjinks/wp-two-factor-auth)
  - [simple-2fa](https://wordpress.org/plugins/simple-2fa/)
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
  - [Elefant CMS](https://github.com/jbroadway/elefant)
  - [OSIRIS](https://github.com/JKoblitz/osiris)
- Articles:
  - [Twilio: How to Create a QR Code in PHP](https://www.twilio.com/blog/create-qr-code-in-php) (featuring v4.3.x)


## Shameless advertising

Hi, please check out some of my other projects that are way cooler than qrcodes!

- [js-qrcode](https://github.com/chillerlan/js-qrcode) - a javascript port of this library
- [php-authenticator](https://github.com/chillerlan/php-authenticator) - a Google Authenticator implementation (see [authenticator example](https://github.com/chillerlan/php-qrcode/blob/main/examples/authenticator.php))
- [php-httpinterface](https://github.com/chillerlan/php-httpinterface) - a PSR-7/15/17/18 implemetation
- [php-oauth-core](https://github.com/chillerlan/php-oauth-core) - an OAuth 1/2 client library along with a bunch of [providers](https://github.com/chillerlan/php-oauth-providers)
- [php-database](https://github.com/chillerlan/php-database) - a database client & querybuilder for MySQL, Postgres, SQLite, MSSQL, Firebird
- [php-tootbot](https://github.com/php-tootbot/tootbot-template) - a Mastodon bot library
