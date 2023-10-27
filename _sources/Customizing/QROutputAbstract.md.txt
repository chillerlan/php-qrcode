# `QROutputAbstract`

The abstract class `QROutputAbstract` contains several commonly used methods and properties and can be used as a basis for a custom output class.


## Properties

### `$options` and `$matrix`

The `QROptions` and `QRMatrix` instances that were passed to the constructor of the output class.
Both objects can be modified during runtime, for example to override settings or add matrix modifications.


### `$moduleCount`, `$scale` and `$length`

These are convenience variables mostly to avoid multiple method calls to `QRMatrix::getSize()` and `QROptions::__get('scale')` inside loops,
the `$length` is calculated from the aforementioned values (`$moduleCount * $scale`).
The method `setMatrixDimensions()` can be called to update these 3 values after the matrix has been modified, e.g. by adding a quiet zone during output.


### `$moduleValues`

The finalized map of `$M_TYPE` to value for the current output. This map is generated during invocation of the output class via `setModuleValues()`.


### Copies of `QROptions` values

Some values from the `QROptions` instance are copied to properties to avoid calling the magic getters in long loops for a significant performance increase, e.g. in the module collector.
Currently, the following values are copied via `copyVars()` during invocation: `$connectPaths`, `$excludeFromConnect`, `$eol`,
`$drawLightModules`, `$drawCircularModules`, `$keepAsSquare`, `$circleRadius` (and additionally `$circleDiameter`).


## Methods

### `setModuleValues()`

This method calls the abstract/interface methods `moduleValueIsValid()`,  `prepareModuleValue()` and `getDefaultModuleValue()` to prepare the module values map.


### `moduleValueIsValid()`

This method is declared in the `QROutputInterface` and needs to be implemented by the output class; it is `static` so that it can be called before invocation.
The purpose is to determine whether the given `mixed` input is a valid module value for the current output class and returns `bool`.
It's also useful to check values from `QROptions` such as `$bgColor` or `$transparencyColor`.

Below is a pseudo implementation, check the code of the several output classes for actual implementations
(e.g. [`QRImagick::moduleValueIsValid()`](https://github.com/chillerlan/php-qrcode/blob/4bd4b59fdec72397f5b1f70da9cadcb76764191b/src/Output/QRImagick.php#L68-L96))

```php
class MyOutput extends QROutputAbstract{

	public static function moduleValueIsValid(mixed $value):bool{

		// check the type of the input value first
		if(!is_expected_type($value)){
			return false;
		}

		// do some more checks to determine the value
		if(!is_somehow_valid($value)){
			return false;
		}

		// looks like we got a match
		return true;
	}

}
```


### `prepareModuleValue()`

This method prepares the final replacement value from the given input.
It might still be necessary to validate the given value despite it being checked earlier by `moduleValueIsValid()` -
if nothing helps, this is a good place to throw an exception.
Below a pseudo implementation example (see [`QRGdImage::prepareModuleValue()`](https://github.com/chillerlan/php-qrcode/blob/4bd4b59fdec72397f5b1f70da9cadcb76764191b/src/Output/QRGdImage.php#L138-L158)):

```php
class MyOutput extends QROutputAbstract{

	protected function prepareModuleValue(mixed $value):mixed{

		// extended validation to make sure the values are valid for output
		// e.g. examine array values, clamp etc.
		if(!is_valid($value)){
			throw new QRCodeOutputException('invalid module value');
		}

		return $this->modifyValue($value);
	}

}
```


### `getDefaultModuleValue()`

Finally, setting a default value is required, in case a value for an `$M_TYPE` is not set or it's invalid.

```php
class MyOutput extends QROutputAbstract{

	protected function getDefaultModuleValue(bool $isDark):mixed{
		$defaultValue = ($isDark === true)
			? 'default value for dark'
			: 'default value for light';

		return $this->prepareModuleValue($defaultValue);
	}

}
```


### `getModuleValue()` and `getModuleValueAt()`

Both methods return a module value, the main difference is that `getModuleValueAt()` is a convenience method
that makes an extra call to retrieve the `$M_TYPE` from the given matrix coordinate to return the value via `getModuleValue()`.

A `foreach` loop over the matrix gives you the key (coordinate) *and* value of an array element:

```php
class MyOutput extends QROutputAbstract{

	public function dump(string $file = null):string{
		$lines = [];

		foreach($this->matrix->getMatrix() as $y => $row){
			$lines[$y] = '';

			foreach($row as $x => $M_TYPE){
				$lines[$y] .= $this->getModuleValue($M_TYPE);
			}
		}

		return implode($this->options->eol, $lines);
	}

}
```

However, sometimes you might happen to use a `for` loop instead. The `for` loop leaves you only with the matrix coordinates, so you need to call `getModuleValueAt()`:

```php
class MyOutput extends QROutputAbstract{

	public function dump(string $file = null):string{
		$lines = [];

		for($y = 0; $y < $this->moduleCount; $y++){
			$lines[$y] = '';

			for($x = 0; $x < $this->moduleCount; $x++){
				$lines[$y] .= $this->getModuleValueAt($x, $y);
			}

		}

		return implode($this->options->eol, $lines);
	}

}
```


### `setMatrixDimensions()`

As mentioned before, this method is supposed to set the values for the properties `$moduleCount`, `$scale` and `$length`.
It is called in the constructor during invocation, but it might be necessary to call it again if the size of the matrix was changed in the output class
(see [the round quiet zone example](https://github.com/chillerlan/php-qrcode/blob/99b1f9cf454ab1316cb643950a71caed3a6c0f5a/examples/svgRoundQuietzone.php#L38-L44) for a use case).


### `getOutputDimensions()`

This method provides a simple way for consistent width/height values for the output (if applicable) which then can be changed by simply overriding this method.
It returns a 2-element array that contains the values in a format that can be used by the output class, which is `QROutputAbstract::$length` (`$moduleCount * $scale`):

```php
[$width, $height] = $this->getOutputDimensions();
```

The output width and height can be changed in all places by simply overriding the method:

```php
class MyOutput extends QROutputAbstract{

	protected function getOutputDimensions():array{
		// adjust the height in order to add something under the QR Code
		return [$this->length, ($this->length + 69)];
	}

}
```


### `collectModules()`

The module collector is particularly useful for plain text based file formats, for example the various markup languages like SVG and HTML or other structured file formats such as EPS.
This method takes a `Closure` as a parameter, which is called with 4 parameters: the module coordinates `$x` and `$y`, the `$M_TYPE` and `$M_TYPE_LAYER`.
The `$M_TYPE_LAYER` is a copy of the `$M_TYPE` that represents the array key of the returned array and that may have been reassigned in the collector to another path layer, e.g. through `QROptions::$connectPaths`.

```php
class MyOutput extends QROutputAbstract{

	public function dump(string $file = null):string{

		// collect the modules for the path elements
		$paths = $this->collectModules(fn(int $x, int $y, int $M_TYPE):string => sprintf('%d %d %012b', $x, $y, $M_TYPE));

		// loop over the paths
		foreach($paths as $M_TYPE_LAYER => &$path){

			if(empty($path)){
				continue;
			}

			$path = implode($this->options->eol, $path);
		}

		return implode($this->options->eol, $paths);
	}

}
```

Sometimes it can be necessary to override `collectModules()` in order to apply special effects such as random colors - you can find some implementations in [the SVG examples](https://github.com/chillerlan/php-qrcode/tree/main/examples).


### `saveToFile()` and `toBase64DataURI()`

The void method `saveToFile()` takes a data blob and the `$file` given in `QROutputInterface::dump()` and save to the path if it is not `null` - the file path itself is not checked except for writability.

The final output can be transformed to a [base64 data URI](https://en.wikipedia.org/wiki/Data_URI_scheme) with `toBase64DataURI()`, where the data blob and a valid mime type as parameters - the mime type is not checked.



```php
class MyOutput extends QROutputAbstract{

	public function dump(string $file = null):string{
		$output = 'qrcode data string';

		// save the plain data to file
		$this->saveToFile($output, $file);

		// base64 encoding may be called optionally
		if($this->options->outputBase64){
			$output = $this->toBase64DataURI($output, 'text/plain');
		}

		return $output;
	}

}
```
