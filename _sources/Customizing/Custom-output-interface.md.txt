# Custom `QROutputInterface`

Let's suppose that we want to create our own output interface because there's no built-in output class that supports the format we need for our application.
In this example we'll create a string output class that outputs the coordinates for each module, separated by module type.


## Class skeleton

We'll start with a skeleton that extends  `QROutputAbstract` and implements the methods that are required by `QROutputInterface`:

```php
class MyCustomOutput extends QROutputAbstract{

	public static function moduleValueIsValid($value):bool{}

	protected function prepareModuleValue($value){}

	protected function getDefaultModuleValue(bool $isDark){}

	public function dump(string $file = null){}

}
```


## Module values

The validator should check whether the given input value and range is valid for the output class and if it can be given to the `QROutputAbstract::prepareModuleValue()` method.
For example in the built-in GD output it would check if the value is an array that has a minimum of 3 elements (for RGB), each of which is numeric.

In this example we'll accept string values, the characters `a-z` (case-insensitive) and a hyphen `-`:

```php
	public static function moduleValueIsValid($value):bool{
		return is_string($value) && preg_match('/^[a-z-]+$/i', $value) === 1;
	}
```

To prepare the final module substitute, we should transform the given (validated) input value in a way so that it can be accessed without any further calls or transformation.
In the built-in output for example this means it would return an `ImagickPixel` instance or the integer value returned by `imagecolorallocate()` on the current `GdImage` instance.

For our example, we'll lowercase the validated string:

```php
	protected function prepareModuleValue($value):string{
		return strtolower($value);
	}
```

Finally, we need to provide a default value for dark and light, we can call `prepareModuleValue()` here if necessary.
We'll return an empty string `''` as we're going to use the `QROutputInterface::LAYERNAMES` constant for non-existing values
(returning `null` would run into an exception in `QROutputAbstract::getModuleValue()`).

```php
	protected function getDefaultModuleValue(bool $isDark):string{
		return '';
	}
```


## Transform the output

In our example, we want to collect the modules by type and have the collections listed under a header for each type.
In order to do so, we need to collect the modules per `$M_TYPE` before we can render the final output.

```php
	public function dump(string $file = null):string{
		$collections = [];

		// loop over the matrix and collect the modules per layer
		foreach($this->matrix->getMatrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$collections[$M_TYPE][] = $this->module($x, $y, $M_TYPE);
			}
		}

		// build the final output
		$out = [];

		foreach($collections as $M_TYPE => $collection){
			$name  = ($this->getModuleValue($M_TYPE) ?: $this::LAYERNAMES[$M_TYPE]);
			// the section header
			$out[] = sprintf("%s (%012b)\n", $name, $M_TYPE);
			// the list of modules
			$out[] = sprintf("%s\n", implode("\n", $collection));
		}

		return implode("\n", $out);
	}
```

We've introduced another method that handles the module rendering, which incooperates handling of the `QROptions::$drawLightModules` setting:

```php
	protected function module(int $x, int $y, int $M_TYPE):string{

		if(!$this->drawLightModules && !$this->matrix->isDark($M_TYPE)){
			return '';
		}

		return sprintf('x: %s, y: %s', $x, $y);
	}
```

Speaking of option settings, there's also `QROptions::$connectPaths` which we haven't taken care of yet - the good news is that we don't need to as it is already implemented!
We'll modify the above `dump()` method to use `QROutputAbstract::collectModules()` instead.

The module collector accepts a `Closure` as its only parameter, which is called with 4 parameters:

- `$x`           : current column
- `$y`           : current row
- `$M_TYPE`      : field value
- `$M_TYPE_LAYER`: (possibly modified) field value that acts as layer id

We only need the first 3 parameters, so our closure would look as follows:

```php
$closure = fn(int $x, int $y, int $M_TYPE):string => $this->module($x, $y, $M_TYPE);
```

As of PHP 8.1+ we can narrow this down with the [first class callable syntax](https://www.php.net/manual/en/functions.first_class_callable_syntax.php):

```php
$closure = $this->module(...);
```

This is our final output method then:

```php
	public function dump(string $file = null):string{
		$collections = $this->collectModules($this->module(...));

		// build the final output
		$out = [];

		foreach($collections as $M_TYPE => $collection){
			$name  = ($this->getModuleValue($M_TYPE) ?: $this::LAYERNAMES[$M_TYPE]);
			// the section header
			$out[] = sprintf("%s (%012b)\n", $name, $M_TYPE);
			// the list of modules
			$out[] = sprintf("%s\n", implode("\n", $collection));
		}

		return implode("\n", $out);
	}
```


## Run the custom output

To run the output we just need to set the `QROptions::$outputInterface` to our custom class:

```php
$options = new QROptions;
$options->outputType       = QROutputInterface::CUSTOM;
$options->outputInterface  = MyCustomOutput::class;
$options->connectPaths     = true;
$options->drawLightModules = true;

// our custom module values
$options->moduleValues    = [
	QRMatrix::M_DATA      => 'these-modules-are-light',
	QRMatrix::M_DATA_DARK => 'here-is-a-dark-module',
];

$qrcode = new QRCode($options);
$qrcode->addByteSegment('test');

var_dump($qrcode->render());
```

The output looks similar to the following:
```
these-modules-are-light (000000000010)

x: 0, y: 0
x: 1, y: 0
x: 2, y: 0
...

here-is-a-dark-module (100000000010)

x: 4, y: 4
x: 5, y: 4
x: 6, y: 4
...
```

Profit!


## Summary

We've learned how to create a custom output class for a string based format similar to several of the built-in formats such as SVG or EPS.

The full code of our custom class below:

```php
class MyCustomOutput extends QROutputAbstract{

	protected function prepareModuleValue($value):string{
		return strtolower($value);
	}

	protected function getDefaultModuleValue(bool $isDark):string{
		return '';
	}

	public static function moduleValueIsValid($value):bool{
		return is_string($value) && preg_match('/^[a-z-]+$/i', $value) === 1;
	}

	public function dump(string $file = null):string{
		$collections = $this->collectModules($this->module(...));

		// build the final output
		$out = [];

		foreach($collections as $M_TYPE => $collection){
			$name  = ($this->getModuleValue($M_TYPE) ?: $this::LAYERNAMES[$M_TYPE]);
			// the section header
			$out[] = sprintf("%s (%012b)\n", $name, $M_TYPE);
			// the list of modules
			$out[] = sprintf("%s\n", implode("\n", $collection));
		}

		return implode("\n", $out);
	}

	protected function module(int $x, int $y, int $M_TYPE):string{

		if(!$this->drawLightModules && !$this->matrix->isDark($M_TYPE)){
			return '';
		}

		return sprintf('x: %s, y: %s', $x, $y);
	}

}
```
