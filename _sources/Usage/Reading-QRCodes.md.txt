# Reading QR Codes

## Basic usage

The QR Code reader can be called either from a `QRCode` instance or invoked directly via the `Decoder` class with a `QROptions` (`QRCodeReaderOptionsTrait`) instance.

```php
$options = new QROptions

$options->readerUseImagickIfAvailable = true;
$options->readerIncreaseContrast      = true;
$options->readerGrayscale             = true;
$options->readerInvertColors          = false;
```

The option `QROptions::$readerUseImagickIfAvailable` is exclusive to the `QRCode` instance in order to decide which `LuminanceSourceInterface` to use.
The `QRCode` instance has 3 convenience methods related to the reader:
```php
$qrcode = new QRCode($options)

$result = $qrcode->readFromFile('path/to/qrcode.png');
$result = $qrcode->readFromBlob($imagedata);

// from a luminance source instance
$source = IMagickLuminanceSource::fromBlob($imagedata, $options);
$result = $qrcode->readFromSource($source);
```

## The `LuminanceSourceInterface`

The method `QRCode::readFromSource()` takes a `LuminanceSourceInterface` instance as parameter and is mainly for internal use, as you can invoke and call the decoder directly with it.
Each `LuminanceSourceInterface` has the static convenience methods `fromFile()` and `fromBlob()` that will invoke the instance with the respective parameters, alternatively the instance(s) can be invoked manually:

from an `Imagick` instance:
```php
$imagick = new Imagick;
$imagick->readImageBlob($imagedata);

$source = new IMagickLuminanceSource($imagick, $options);
```

from a `GdImage` instance:

```php
$gdimage = imagecreatefromstring($imagedata);

$source = new GDLuminanceSource($gdimage, $options);
```

## The `Decoder`

The `Decoder` takes a `QROptions` instance as parameter, which currently has no use - it is only handed over for possible future uses.

```php
$result = (new Decoder($options))->decode($source);
```

That is all! The decoder will either return a `DecoderResult` instance or throw an exception.
It is generally a good practice to wrap the reading in a try/catch block:

```php
try{
	$result = $qrcode->readFromFile('path/to/file.png'); // -> DecoderResult

	// ... do stuff with the result
}
catch(QRCodeDecoderException){
	// ... adjust input image (position, contrast, invert, sharpen) and repeat the process
}
```

You can now use the result instance:

```php
$content = $result->data;
// ...or simply cast it to string:
$content = (string)$result;
```

The result instance also holds `Version`, `EccLevel`, `MaskPattern` and `BitBuffer` instances, as well as an array of `FinderPattern` instances,
it also offers a method that returns a `QRMatrix` instance populated with the detected settings:

```php
$matrix = $result->getQRMatrix();

// ...matrix modification...

$output = (new QRCode($options))->renderMatrix($matrix);

// ...dump output
```


## General considerations

The QR Code reader reads the given QR symbol in a single pass, unlike e.g. a reader app on a mobile device with a camera, which repeats the reading process for a sequence of frames from the video input and takes the "best" result.
It means that this reader may fail to properly decode a symbol that reads perfectly fine on a mobile - you'd need to emulate the same process of sequential reading while adjusting the input image to get a similar result.

Further it seems ([per observation](https://github.com/chillerlan/php-qrcode/blob/92346420a5a88aeeb8dc16f731ef1f93331635d3/tests/QRCodeReaderTestAbstract.php#L168-L172)) that the reader favors smaller module sizes (1-2 pixel side length) from version 20 onwards.
The test data set seemed to randomly produce errors depending on the given module scale, independent of the luminance source.
However, scaling down to get a smaller module size isn't the best solution as it may produce additional challenges due to filter artifacts.
