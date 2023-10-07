# Installation

## Installation with Composer

**[Composer](https://getcomposer.org) is required to install this package. Please do not open an issue to complain about "monopolizing the implementation" or similar - we've been there before.**


### composer.json

Installation via [`composer.json`](https://getcomposer.org/doc/04-schema.md):

```json
{
	"require": {
		"php": "^7.4",
		"chillerlan/php-qrcode": "dev-main"
	}
}
```

Note: replace `dev-main` with a [version constraint](https://getcomposer.org/doc/articles/versions.md#writing-version-constraints), e.g. `^4.3` - see [releases](https://github.com/chillerlan/php-qrcode/releases) for valid versions.
In case you want to keep using `dev-main`, specify the hash of a commit to avoid running into unforseen issues, like so: `dev-main#cb69751c3bc090a7fdd2f2601bbe10f28d225f10`


#### Version switch

If your application supports older PHP versions and uses the basic `QRCode` syntax `(new QRCode)->render($data)`, then you can add a version switch to your `composer.json` to allow installing a `php-qrcode` version that suits the platform it runs on:

```json
{
	"require": {
		"php": "^7.0 || ^8.0",
		"chillerlan/php-qrcode": "^2.0 || ^3.4 || ^4.3 || ^5.0"
	}
}
```

Most of the v2.0 API remains unchanged throughout the several versions up to v5.x, however, please test and verify the expected output before you deploy such a switch.


### Terminal

To install `php-qrcode` on the terminal, use:

```shell
composer require chillerlan/php-qrcode
```

If you want to install the package from a specific tag or commit, do as follows:

```shell
composer require chillerlan/php-qrcode:4.3.4
composer require chillerlan/php-qrcode:dev-main#f15b0afe9d4128bf734c3bf1bcffae72bf7b3e53
```


## Manual installation

Download the desired version of the package from [main](https://github.com/chillerlan/php-qrcode/archive/refs/heads/main.zip) or
[release](https://github.com/chillerlan/php-qrcode/releases) and extract the contents to your project folder.
After that, run `composer install` in the package root directory to install the required dependencies and generate `./vendor/autoload.php`.

Profit!


### Can i use this library without using composer?

You can, but it's absolutely not recommended, nor supported.

With that said, I'll leave you with this info:

- download the .zip for a version of your choice and also all required dependencies listed in the `composer.json` for that version (you can find links to the respective repos [on packagist](https://packagist.org/packages/chillerlan/php-qrcode))
- extract the files into your library folder
- include the files manually or with whatever autoloader you are using

Good luck!


## Supported PHP versions & extension requirements

The PHP built-in extensions [GdImage](https://www.php.net/manual/book.image.php) and [mbstring](https://www.php.net/manual/book.mbstring.php) are used across all versions, [ImageMagick](https://www.php.net/manual/book.imagick.php) is optional since v3.x.

| version | branch/tag                                                           | PHP              | supported | required extensions | optional extensions                                                                | info                      |
|---------|----------------------------------------------------------------------|------------------|-----------|---------------------|------------------------------------------------------------------------------------|---------------------------|
| **v5**  | [`dev-main`](https://github.com/chillerlan/php-qrcode/tree/main)     | `^7.4 \|\| ^8.0` | yes       | `mbstring`          | `gd` or `imagick` required for reading QR Codes, `fileinfo` is used in `QRImagick` |                           |
| **v4**  | [`4.3.4`](https://github.com/chillerlan/php-qrcode/tree/v4.3.x)      | `^7.4 \|\| ^8.0` | yes       | `gd`, `mbstring`    | `imagick`                                                                          |                           |
| **v3**  | [`3.4.1`](https://github.com/chillerlan/php-qrcode/tree/v3.2.x)      | `^7.2`           | no        | `gd`, `mbstring`    | `imagick`                                                                          | v3.4.1 also supports PHP8 |
| **v2**  | [`2.0.8`](https://github.com/chillerlan/php-qrcode/tree/v2.0.x)      | `>=7.0.3`        | no        | `gd`, `mbstring`    |                                                                                    |                           |
| **v1**  | [`1.0.9`](https://github.com/chillerlan/php-qrcode/tree/v2.0.x-php5) | `>=5.6`          | no        | `gd`, `mbstring`    |                                                                                    | please let PHP 5 die!     |

PSA: [PHP versions < 8.0 are EOL](https://www.php.net/supported-versions.php) and therefore the respective `QRCode` versions are also no longer supported!


## ImageMagick

Please follow the installation guides for your operating system:
- ImageMagick: [imagemagick.org/script/download.php](https://imagemagick.org/script/download.php)
- PHP `ext-imagick`: [github.com/Imagick/imagick](https://github.com/Imagick/imagick) ([Windows downloads](https://mlocati.github.io/articles/php-windows-imagick.html))
