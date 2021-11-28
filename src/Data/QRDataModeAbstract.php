<?php
/**
 * Class QRDataModeAbstract
 *
 * @created      19.11.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

/**
 */
abstract class QRDataModeAbstract implements QRDataModeInterface{

	/**
	 * the current data mode: Num, Alphanum, Kanji, Byte
	 */
	protected static int $datamode;

	/**
	 * The data to write
	 */
	protected string $data;

	/**
	 * QRDataModeAbstract constructor.
	 *
	 * @throws \chillerlan\QRCode\Data\QRCodeDataException
	 */
	public function __construct(string $data){

		if(!$this::validateString($data)){
			throw new QRCodeDataException('invalid data');
		}

		$this->data = $data;
	}

	/**
	 * returns the character count of the $data string
	 */
	protected function getCharCount():int{
		return strlen($this->data);
	}

	/**
	 * @inheritDoc
	 */
	public function getDataMode():int{
		return $this::$datamode;
	}

}
