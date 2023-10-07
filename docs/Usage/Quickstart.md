# Quickstart

## Import the library

Import the main class(es) and include the autoloader (if necessary):
```php
use chillerlan\QRCode\{QRCode, QROptions};

require_once __DIR__.'/../vendor/autoload.php';
```


## Create your first QR Code

We want to encode this URI for a mobile authenticator into a QRcode image:
```php
$data   = 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net';
$qrcode = (new QRCode)->render($data);

// default output is a base64 encoded data URI
printf('<img src="%s" alt="QR Code" />', $qrcode);
```


### Configuration

Configuration using `QROptions`:

```php
$options = new QROptions;
$options->version      = 7;
$options->outputBase64 = false; // output raw image instead of base64 data URI

header('Content-type: image/svg+xml'); // the image type is SVG by default

echo (new QRCode($options))->render($data);
```

See [Advanced usage](../Usage/Advanced-usage.md) for a more in-depth usage guide
and [configuration settings](../Usage/Configuration-settings.md) for a list of available options.
Also, have a look [in the examples folder](https://github.com/chillerlan/php-qrcode/tree/main/examples) for some more usage examples.


## Reading QR Codes

Using the built-in QR Code reader is pretty straight-forward:
```php
try{
	$result = (new QRCode)->readFromFile('path/to/file.png'); // -> DecoderResult

	// you can now use the result instance...
	$content = $result->data;

	// ...or simply cast the result instance to string to get the content
	$content = (string)$result;
}
catch(Throwable $exception){
	// handle exception...
}
```
It's generally a good idea to wrap the reading in a try/catch block to handle any errors that may occur in the process.


## Notes
The QR encoder, especially the subroutines for mask pattern testing, can cause high CPU load on increased matrix size.
You can avoid a part of this load by choosing a fast output module, like SVG.
Oh hey and don't forget to sanitize any user input!
