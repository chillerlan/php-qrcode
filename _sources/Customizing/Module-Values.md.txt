# Module values

## Basics

The QR Code matrix is a 2-dimensional array of numerical values that hold a bitmask for
each QR pixel ("module" as per specification), the so-called "module type" or `$M_TYPE`, which is represented by
[the `QRMatrix::M_*` constants](https://chillerlan.github.io/php-qrcode/classes/chillerlan-QRCode-Data-QRMatrix.html#toc-constants).
The bitmask is 12 bits wide; the first 11 bits stand for the pattern type, the highest bit designates whether the module is dark or light.
You can assign different values for the several [function patterns](../Appendix/Terminology.md#function-patterns) to colorize them or even draw pixel-art.


## Assigning values

To map the values and properly render the modules for the given `QROutputInterface`, it may be necessary to overwrite the
[default values](https://chillerlan.github.io/php-qrcode/classes/chillerlan-QRCode-Output-QROutputInterface.html#constant_DEFAULT_MODULE_VALUES),
that are replaced by the user defined values in `QROptions::$moduleValues` during the render process.

The map of `QRMatrix::M_*` constants => default values looks similar to the following
(values with "_DARK" and "_LIGHT" suffix are for convenience):

```php
$options->moduleValues = [
	// light
	QRMatrix::M_NULL             => false, // special value - always 0
	QRMatrix::M_DARKMODULE_LIGHT => false, // key equivalent to (QRMatrix::M_DARKMODULE & ~QRMatrix::IS_DARK)
	QRMatrix::M_DATA             => false,
	QRMatrix::M_FINDER           => false,
	QRMatrix::M_SEPARATOR        => false,
	QRMatrix::M_ALIGNMENT        => false,
	QRMatrix::M_TIMING           => false,
	QRMatrix::M_FORMAT           => false,
	QRMatrix::M_VERSION          => false,
	QRMatrix::M_QUIETZONE        => false,
	QRMatrix::M_LOGO             => false,
	QRMatrix::M_FINDER_DOT_LIGHT => false,
	// dark
	QRMatrix::M_DARKMODULE       => true,
	QRMatrix::M_DATA_DARK        => true, // key equivalent to (QRMatrix::M_DATA | QRMatrix::IS_DARK)
	QRMatrix::M_FINDER_DARK      => true,
	QRMatrix::M_SEPARATOR_DARK   => true,
	QRMatrix::M_ALIGNMENT_DARK   => true,
	QRMatrix::M_TIMING_DARK      => true,
	QRMatrix::M_FORMAT_DARK      => true,
	QRMatrix::M_VERSION_DARK     => true,
	QRMatrix::M_QUIETZONE_DARK   => true,
	QRMatrix::M_LOGO_DARK        => true,
	QRMatrix::M_FINDER_DOT       => true,
];
```

Not all the module values need to be specified - missing values will be filled with the internal default values
for `true` (dark) and `false` (light) respectively. The `QROutputInterface` inheritors implement a `moduleValueIsValid()`
method that checks if the given value is valid for that particular class:

```php
// set an initial value that acts as default
$dark = 'rgba(0, 0, 0, 0.5)';

// try to receive user input
if(QRMarkupSVG::moduleValueIsValid($_GET['qr_dark'])){
	// module values for HTML, SVG and other markup may need special treatment,
	// e.g. only accept hexadecimal values from user input
	// as moduleValueIsValid() just checks for the general syntax
	$dark = sanitize_user_input($_GET['qr_dark']);
}

$options->moduleValues = [
	QRMatrix::M_DATA_DARK      => $dark,
	QRMatrix::M_FINDER_DARK    => $dark,
	QRMatrix::M_ALIGNMENT_DARK => $dark,
	QRMatrix::M_FINDER_DOT     => $dark,
];
```

The several output classes may need different substitute values (you can find examples [in the test `moduleValueProvider()` for each output class](https://github.com/chillerlan/php-qrcode/tree/main/tests/Output)):

```php
// for HTML, SVG and ImageMagick
$options->moduleValues = [
	QRMatrix::M_DATA      => '#ffffff',
	QRMatrix::M_DATA_DARK => '#000000',
	// ...
];

// for the GdImage, EPS and FPDF output types
$options->moduleValues = [
	QRMatrix::M_DATA      => [255, 255, 255],
	QRMatrix::M_DATA_DARK => [0, 0, 0],
	// ...
];

// for string/text output
$options->moduleValues = [
	QRMatrix::M_DATA      => '░░',
	QRMatrix::M_DATA_DARK => '██',
	// ...
];
```


## Handling in your own `QROutputInterface`

### Setting module values

[`QROutputAbstract::setModuleValues()`](https://chillerlan.github.io/php-qrcode/classes/chillerlan-QRCode-Output-QROutputAbstract.html#method_setModuleValues)
calls the 3 abstract methods `moduleValueIsValid()`, `getModuleValue()` and `getDefaultModuleValue()` to fill the internal
module value map with the values given via `QROptions::$moduleValues`:

```php
protected function setModuleValues():void{

	foreach($this::DEFAULT_MODULE_VALUES as $M_TYPE => $defaultValue){
		$value = ($this->options->moduleValues[$M_TYPE] ?? null);

		$this->moduleValues[$M_TYPE] = $this->moduleValueIsValid($value)
			? $this->getModuleValue($value)
			: $this->getDefaultModuleValue($defaultValue);
	}

}
```

In the following example we'll create these methods for the `GdImage` output.
Since [`imagecolorallocate()`](https://www.php.net/manual/function.imagecolorallocate) and other GD functions accept 3 values
for RGB color (or 4 in case of RGBA), we'll supply these as a array where each value is an integer between 0 and 255 (`[RRR, GGG, BBB, (, AAA)]`).

First we need to validate the input:

```php
protected function moduleValueIsValid($value):bool{

	// nowhere near valid
	if(!is_array($value) || count($value) !== 3){
		return false;
	}

	// now iterate over the values
	foreach($value as $color){

		// non-integers won't work
		if(!is_int($color)){
			return false;
		}

		// a strict check - we could also just ignore outliers and clamp the values instead
		if($color < 0 || $color > 255){
			return false;
		}
	}

	return true; // yay!
}
```

Now we can prepare the value:

```php
protected function getModuleValue($value):array{
	// we call array_values() so we don't run into string-key related issues
	return array_map(fn(int $val):int => max(0, min(255, $val)), array_values($value));
}
```

And finally we need to provide default values:

```php
protected function getDefaultModuleValue(bool $isDark):array{
	return $isDark ? [0, 0, 0] : [255, 255, 255];
}
```

Now that everything is ready and set, we can use the values in our GD functions:

```php
$color = imagecolorallocate($this->image, ...$this->moduleValues[$M_TYPE]);
```


### Using the module values

The state of the `$M_TYPE` is set with the `QRMatrix::IS_DARK` constant:

```php
// set to dark (true) with bitwise OR:
$M_TYPE = ($M_TYPE | QRMatrix::IS_DARK);

// set to light (false) with bitwise AND NOT
$M_TYPE = ($M_TYPE & ~QRMatrix::IS_DARK);

// toggle the opposite state with bitwise XOR
$M_TYPE = ($M_TYPE ^ QRMatrix::IS_DARK);
```

You can manually check whether the module is dark:

```php
($value & QRMatrix::IS_DARK) === QRMatrix::IS_DARK;
```

However it is much more convenient to use the `QRMatrix` methods for that:

```php
for($y = 0; $y < $this->moduleCount; $y++){ // rows
	for($x = 0; $x < $this->moduleCount; $x++){ // columns
		// sets current module as dark (true) with the M_DATA type
		$this->matrix->set($x, $y, true, QRMatrix::M_DATA);

		// -> true (shortcut for checkType($x, $y, QRMatrix::IS_DARK))
		$this->matrix->check($x, $y);

		// -> true (current module is of type M_DATA)
		$this->matrix->checkType($x, $y, QRMatrix::M_DATA);

		// -> true (current module is of type IS_DARK)
		$this->matrix->checkType($x, $y, QRMatrix::IS_DARK);

		// -> false, type is M_DATA
		$this->matrix->checkTypeIn($x, $y, [QRMatrix::M_FINDER_DARK, QRMatrix::M_ALIGNMENT]);
	}
}
```
