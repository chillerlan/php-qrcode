<?php
/**
 * Class ECI
 *
 * @filesource   ECI.php
 * @created      20.11.2020
 * @package      chillerlan\QRCode\Data
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\Helpers\BitBuffer;
use chillerlan\QRCode\Common\Mode;

/**
 * Adds an ECI Designator
 *
 * Please note that you have to take care for the correct data encoding when adding with QRCode::add*Segment()
 */
class ECI extends QRDataModeAbstract{

	public const CP437                 = 0;  // Code page 437, DOS Latin US
	public const ISO_IEC_8859_1_GLI    = 1;  // GLI encoding with characters 0 to 127 identical to ISO/IEC 646 and characters 128 to 255 identical to ISO 8859-1
	public const CP437_WO_GLI          = 2;  // An equivalent code table to CP437, without the return-to-GLI 0 logic
	public const ISO_IEC_8859_1        = 3;  // Latin-1 (Default)
	public const ISO_IEC_8859_2        = 4;  // Latin-2
	public const ISO_IEC_8859_3        = 5;  // Latin-3
	public const ISO_IEC_8859_4        = 6;  // Latin-4
	public const ISO_IEC_8859_5        = 7;  // Latin/Cyrillic
	public const ISO_IEC_8859_6        = 8;  // Latin/Arabic
	public const ISO_IEC_8859_7        = 9;  // Latin/Greek
	public const ISO_IEC_8859_8        = 10; // Latin/Hebrew
	public const ISO_IEC_8859_9        = 11; // Latin-5
	public const ISO_IEC_8859_10       = 12; // Latin-6
	public const ISO_IEC_8859_11       = 13; // Latin/Thai
	// 14 reserved
	public const ISO_IEC_8859_13       = 15; // Latin-7 (Baltic Rim)
	public const ISO_IEC_8859_14       = 16; // Latin-8 (Celtic)
	public const ISO_IEC_8859_15       = 17; // Latin-9
	public const ISO_IEC_8859_16       = 18; // Latin-10
	// 19 reserved
	public const SHIFT_JIS             = 20; // JIS X 0208 Annex 1 + JIS X 0201
	public const WINDOWS_1250_LATIN_2  = 21; // Superset of Latin-2, Central Europe
	public const WINDOWS_1251_CYRILLIC = 22; // Latin/Cyrillic
	public const WINDOWS_1252_LATIN_1  = 23; // Superset of Latin-1
	public const WINDOWS_1256_ARABIC   = 24;
	public const ISO_IEC_10646_UCS_2   = 25; // High order byte first (UTF-16BE)
	public const ISO_IEC_10646_UTF_8   = 26;
	public const ISO_IEC_646_1991      = 27; // International Reference Version of ISO 7-bit coded character set (US-ASCII)
	public const BIG5                  = 28; // Big 5 (Taiwan) Chinese Character Set
	public const GB18030               = 29; // GB (PRC) Chinese Character Set
	public const EUC_KR                = 30; // Korean Character Set

	/**
	 * The current encoding
	 */
	protected int $encoding;

	protected int $datamode = Mode::DATA_ECI;

	/**
	 * @inheritDoc
	 */
	public function __construct(int $encoding){
		parent::__construct('');

		$this->encoding = $encoding;
	}

	/**
	 * @inheritDoc
	 */
	public function getLengthInBits():int{
		return 8;
	}

	/**
	 * @inheritDoc
	 */
	public static function validateString(string $string):bool{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function write(BitBuffer $bitBuffer, int $version):void{
		$bitBuffer
			->put($this->datamode, 4)
			->put($this->encoding, 8)
		;
	}

}
