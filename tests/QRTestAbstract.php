<?php
/**
 * Class QRTestAbstract
 *
 * @filesource   QRTestAbstract.php
 * @created      17.11.2017
 * @package      chillerlan\QRCodeTest
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

abstract class QRTestAbstract extends TestCase{

	/**
	 * @var \ReflectionClass
	 */
	protected $reflection;

	/**
	 * @var string
	 */
	protected $FQCN;

	protected function setUp(){
		$this->reflection = new ReflectionClass($this->FQCN);
	}

	/**
	 * @param string $method
	 *
	 * @return \ReflectionMethod
	 */
	protected function getMethod(string $method):ReflectionMethod {
		$method = $this->reflection->getMethod($method);
		$method->setAccessible(true);

		return $method;
	}

	/**
	 * @param string $property
	 *
	 * @return \ReflectionProperty
	 */
	protected function getProperty(string $property):ReflectionProperty{
		$property = $this->reflection->getProperty($property);
		$property->setAccessible(true);

		return $property;
	}

	/**
	 * @param        $object
	 * @param string $property
	 * @param        $value
	 *
	 * @return void
	 */
	protected function setProperty($object, string $property, $value){
		$property = $this->getProperty($property);
		$property->setAccessible(true);
		$property->setValue($object, $value);
	}


}
