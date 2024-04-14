<?php
/**
 * Trait EncodingHandlerTrait
 *
 * @created      12.01.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Common;

use chillerlan\QRCode\QRCodeException;
use Symfony\Polyfill\Mbstring\Mbstring;
use Throwable;
use function class_exists;
use function extension_loaded;

trait EncodingHandlerTrait{

	protected static CharacterEncodingHandlerInterface $encodingHandler;

	/**
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected static function getEncodingHandler():string{

		try{
			return match(true){
				extension_loaded('mbstring')                               => MBStringHandler::class,
				extension_loaded('iconv') && class_exists(Mbstring::class) => MBStringHandler::class,
				extension_loaded('iconv')                                  => IconvHandler::class,
			};
		}
		catch(Throwable){
			throw new QRCodeException('no character encoding handler available');
		}

	}

	/**
	 * We're setting an instance here so that the IDE stops yelling at the FQN
	 */
	protected function setEncodingHandler():void{
		static::$encodingHandler = new (static::getEncodingHandler());
	}

}
