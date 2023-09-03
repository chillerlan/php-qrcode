# QROutputInterface

The full phpDocumentor API documentation can be found at [chillerlan.github.io/php-qrcode](https://chillerlan.github.io/php-qrcode/classes/chillerlan-QRCode-Output-QROutputInterface.html).


##  Methods
<!-- using non-breaking spaces chr(255) in the longest method signature to force the silly table to stretch -->
| method                                | return  | description                                                          |
|---------------------------------------|---------|----------------------------------------------------------------------|
| (static) `moduleValueIsValid($value)` | `bool`  | Checks whether the given value is valid for the current output class |
| `dump(string $file = null)`           | `mixed` | Generates the output, optionally dumps it to a file, and returns it  |


##  Constants

| name                    | description                                        |
|-------------------------|----------------------------------------------------|
| `MARKUP_HTML`           |                                                    |
| `MARKUP_SVG`            |                                                    |
| `GDIMAGE_PNG`           |                                                    |
| `GDIMAGE_JPG`           |                                                    |
| `GDIMAGE_GIF`           |                                                    |
| `STRING_JSON`           |                                                    |
| `STRING_TEXT`           |                                                    |
| `IMAGICK`               |                                                    |
| `FPDF`                  |                                                    |
| `EPS`                   |                                                    |
| `CUSTOM`                |                                                    |
| `MODES`                 | Map of built-in output modes => class FQN          |
| `DEFAULT_MODULE_VALUES` | Map of module type => default value                |
| `LAYERNAMES`            | Map of module type => readable name (for CSS etc.) |
