<?php
/**
 * Trait Container
 *
 * @filesource   Container.php
 * @created      13.11.2017
 * @package      chillerlan\Traits
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Traits;

use ReflectionProperty;

/**
 * a generic container with magic getter and setter
 */
trait Container{

	/**
	 * @param array                          $properties
	 */
	public function __construct(array $properties = null){

		if(!empty($properties)){

			foreach($properties as $key => $value){
				$this->__set($key, $value);
			}

		}

	}

	/**
	 * @param string $property
	 *
	 * @return mixed
	 */
	public function __get($property){

		if($this->__isset($property)){
			return $this->{$property};
		}

		return null;
	}

	/**
	 * @param string $property
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function __set($property, $value){

		// avoid overwriting private properties
		if(!property_exists($this, $property) || !$this->__isPrivate($property)){
			$this->{$property} = $value;
		}

	}

	/**
	 * @param string $property
	 *
	 * @return bool
	 */
	public function __isset($property){
		return (property_exists($this, $property) && !$this->__isPrivate($property));
	}

	/**
	 * @param string $property
	 *
	 * @return bool
	 */
	protected function __isPrivate($property){
		return (new ReflectionProperty($this, $property))->isPrivate();
	}

	/**
	 * @param string $property
	 *
	 * @return void
	 */
	public function __unset($property){

		// avoid unsetting private properties
		if($this->__isPrivate($property)){
			unset($this->{$property});
		}

	}

	/**
	 * @return string
	 */
	public function __toString(){
		return json_encode($this->__toArray());
	}

	/**
	 * @return array
	 */
	public function __toArray(){
		$data = [];

		foreach($this as $property => $value){

			// exclude private properties
			if($this->__isset($property)){
				$data[$property] = $value;
			}

		}

		return $data;
	}

}
