<?php
/**
 * Trait QRAuthenticator
 *
 * @filesource   QRAuthenticator.php
 * @created      21.12.2017
 * @package      chillerlan\QRCode\Traits
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Traits;

use chillerlan\Authenticator\Authenticator;
use chillerlan\QRCode\QRCode;

/**
 * Creates URI QR Codes for use with mmobile authenticators
 */
trait QRAuthenticator{

	/**
	 * @var \chillerlan\QRCode\QROptions
	 */
	protected $qrOptions;

	/**
	 * @var string
	 */
	protected $authenticatorSecret;

	/**
	 * @var int
	 */
	protected $authenticatorDigits = Authenticator::DEFAULT_DIGITS;

	/**
	 * @var int
	 */
	protected $authenticatorPeriod = Authenticator::DEFAULT_PERIOD;

	/**
	 * @var string
	 */
	protected $authenticatorMode   = Authenticator::DEFAULT_AUTH_MODE;

	/**
	 * @var string
	 */
	protected $authenticatorAlgo   = Authenticator::DEFAULT_HASH_ALGO;

	/**
	 * @param string $label
	 * @param string $issuer
	 *
	 * @return mixed
	 */
	protected function getURIQRCode(string $label, string $issuer) {
		$uri = $this->getAuthenticator()->setSecret($this->authenticatorSecret)->getUri($label, $issuer);

		return (new QRCode($this->qrOptions))->render($uri);
	}

	/**
	 * @return \chillerlan\Authenticator\Authenticator
	 */
	protected function getAuthenticator():Authenticator {
		return (new Authenticator)
			->setPeriod($this->authenticatorPeriod)
			->setDigits($this->authenticatorDigits)
			->setMode($this->authenticatorMode)
			->setAlgorithm($this->authenticatorAlgo)
		;
	}

}
