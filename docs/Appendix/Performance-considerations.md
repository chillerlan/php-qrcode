# Performance considerations

Generating a QR Code is not a trivial task - it is a combination of countless complex mathematical operations on top of rendering the output.

This library seeks not to be the fastest QR Code generator, but instead to provide flexibility and user friendlyness,
which in turn comes with a slight performance cost.


## Version

The [version of the QR symbol](./Terminology.md#version) is one of the major performance factors as it
determines the size of the symbol and therefore the amount of data that can be stored. Iterating over the internal
representation of the matrix takes more time with increasing size and the internals iterate over the matrix a LOT.

Because of that, you want to select the smallest possible version for the given data of course, which the encoder does by default.
However, sometimes the possibly varying size of the symbol may not be desired and you want to choose a fixed size, in which case
you should determine the maximum size of the input data and choose a version that fits.


## Ecc level

Another factor is the [ECC level](./Terminology.md#ecc-error-correction-coding) that determines the error correction capacity. The default setting is the lowest capacity (L, 7%)
which allows the highest amount of data to be stored and which is good enough for e.g. on-screen display or poster prints.
ECC level H on the other hand allows for up to 30% error correction capacity, which is great for "high risk" applications such as prints on mail.
With increasing error correction capacity, the maximum amount of data a symbol can hold decreases, and a higher version number may be necessary.


## Data mode

By default, the encoder auto-detects the best [mode for data encoding](Terminology.md#mode) (numeric, alphanumeric, kanji, hanzi or 8-bit binary)
and depending on the length of the given data, the detection costs an increasing amount of time. To circumvent this,
you can call one of the "add segment" methods on the `QRCode` instance, for example: `$qrcode->addByteSegment($data)`.

Generally, using 8-bit binary mode (or just "byte mode") is the fast and fail-safe mode for any kind of data, and with
[ECI](https://en.wikipedia.org/wiki/Extended_Channel_Interpretation) it even offers support for character sets other than UTF-8.
So, unless you want to fit a large amount of japanese or chinese characters into a QR symbol of a certain version,
encoding those characters as 3 or 4 byte UTF-8 may still be faster in 8-bit byte than in the "compressed" 13-bit double byte modes.


## Mask pattern

[Evaluating the QR symbol](./Terminology.md#data-masking) in order to pick the right [mask pattern](./Terminology.md#mask-pattern)
is a complex and costly operation that is necessary to ensure the symbol is readable. Although [there is an option](../Usage/Configuration-settings.md#maskpattern)
to override the evaluation and manually set a mask pattern, this is not recommended unless you know exactly what you're doing
as it can render a QR symbol unreadable.

The table below shows the performance impact (in miliseconds) of the mask pattern evaluation for each version, the times may vary between systems.

| version   |       1 |       2 |       3 |       4 |       5 |       6 |       7 |       8 |       9 |      10 |
|-----------|--------:|--------:|--------:|--------:|--------:|--------:|--------:|--------:|--------:|--------:|
| **1-10**  |   4.414 |   5.697 |   7.986 |   9.221 |  10.877 |  11.293 |  13.901 |  15.563 |  18.142 |  20.501 |
| **11-20** |  22.662 |  27.779 |  29.622 |  33.017 |  36.358 |  39.712 |  43.685 |  47.121 |  51.389 |  57.865 |
| **21-30** |  59.753 |  68.502 |  68.523 |  72.866 |  78.245 |  83.593 |  88.327 |  94.921 | 103.394 | 106.358 |
| **31-40** | 113.311 | 120.484 | 126.215 | 132.931 | 139.783 | 145.617 | 170.576 | 165.996 | 167.365 | 175.821 |


## Output

Output rendering depends heavily on the size of the QR matrix, the desired type and the underlying libraries and/or PHP extensions.
Especially the rendering of raster images through GD or ImagMagick can be very slow, depending on [the scale setting](../Usage/Configuration-settings.md#scale),
filters and image type.

Below a comparison of the performance for the several built-in output classes (times in miliseconds, scale = 5):

|                   |     v5 |    v10 |     v15 |     v20 |     v25 |     v30 |     v35 |     v40 |
|-------------------|-------:|-------:|--------:|--------:|--------:|--------:|--------:|--------:|
| **QRMarkupSVG**   |  3.732 |  8.645 |  13.846 |  21.127 |  32.842 |  43.753 |  56.584 |  73.885 |
| **QRMarkupHTML**  |  0.522 |  1.308 |   2.062 |   2.761 |   3.907 |   5.201 |   7.931 |   9.572 |
| **QRGdImageBMP**  |  5.998 | 12.541 |  20.728 |  32.336 |  46.345 |  62.842 |  81.555 | 106.482 |
| **QRGdImageGIF**  |  3.427 |  6.817 |  12.226 |  17.925 |  25.453 |  35.136 |  44.706 |  57.477 |
| **QRGdImageJPEG** |  2.284 |  4.882 |   8.161 |  12.097 |  17.333 |  23.862 |  30.327 |  40.226 |
| **QRGdImagePNG**  |  4.523 |  9.377 |  16.581 |  26.207 |  36.516 |  49.066 |  63.765 |  82.074 |
| **QRGdImageWEBP** |  8.211 | 17.367 |  30.079 |  47.095 |  69.668 |  91.378 | 119.869 | 150.288 |
| **QRStringJSON**  |  0.043 |  0.066 |   0.107 |   0.158 |   0.215 |   0.301 |   0.369 |   0.492 |
| **QRStringText**  |  0.229 |  0.387 |   0.628 |   0.952 |   1.312 |   1.759 |   2.329 |   3.045 |
| **QRImagick**     | 37.694 | 68.808 | 114.415 | 172.962 | 242.338 | 325.085 | 419.999 | 529.897 |
| **QRFpdf**        |  6.578 | 12.466 |  21.169 |  33.021 |  45.469 |  61.198 |  80.092 | 100.059 |
| **QREps**         |  1.269 |  2.694 |   4.515 |   6.933 |  11.049 |  14.181 |  20.799 |  25.886 |
