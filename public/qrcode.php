<?php
/**
 * @filesource   qrcode.php
 * @created      18.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodePublic;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once '../vendor/autoload.php';

try{

	$moduleValues = [
		// finder
		1536 => $_POST['m_finder_dark'],
		6    => $_POST['m_finder_light'],
		// alignment
		2560 => $_POST['m_alignment_dark'],
		10   => $_POST['m_alignment_light'],
		// timing
		3072 => $_POST['m_timing_dark'],
		12   => $_POST['m_timing_light'],
		// format
		3584 => $_POST['m_format_dark'],
		14   => $_POST['m_format_light'],
		// version
		4096 => $_POST['m_version_dark'],
		16   => $_POST['m_version_light'],
		// data
		1024 => $_POST['m_data_dark'],
		4    => $_POST['m_data_light'],
		// darkmodule
		512  => $_POST['m_darkmodule_dark'],
		// separator
		8    => $_POST['m_separator_light'],
		// quietzone
		18   => $_POST['m_quietzone_light'],
	];

	$moduleValues = array_map(function($v){
		#var_dump(array_map('hexdec', unpack('a2r/a2g/a2b','ff00ff')));

		if(preg_match('/[a-f\d]{6}/i', $v) === 1){
			return '#'.$v;
		}

		return strpos($v, '_dark') !== false
			? '#111111'
			: '#eeeeee';

	}, $moduleValues);


	$ecc = in_array($_POST['ecc'], ['L', 'M', 'Q', 'H'], true) ? $_POST['ecc'] : 'L';

	$qro = new QROptions;

	$qro->version       = (int)$_POST['version'];
	$qro->eccLevel      = constant('chillerlan\\QRCode\\QRCode::ECC_'.$ecc);
	$qro->moduleValues  = $moduleValues;
	$qro->outputType    = $_POST['output_type'];
	$qro->maskPattern   = (int)$_POST['maskpattern'];
	$qro->addQuietzone  = isset($_POST['quietzone']);
	$qro->quietzoneSize = (int)$_POST['quietzonesize'];
	$qro->scale         = (int)$_POST['scale'];

	$qrcode = (new QRCode($qro))->render($_POST['inputstring']);

	send_response(['qrcode' => $qrcode]);
}
// Pokémon exception handler
catch(\Exception $e){
	header('HTTP/1.1 500 Internal Server Error');
	send_response(['error' => $e->getMessage()]);
}

/**
 * @param array $response
 */
function send_response(array $response){
	header('Content-type: application/json;charset=utf-8;');
	echo json_encode($response);
	exit;
}
