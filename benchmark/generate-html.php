<?php
/**
 * Generates a benchmark report in HTML
 *
 * @created      30.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeBenchmark;

use RuntimeException;
use function array_keys;
use function copy;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function htmlspecialchars;
use function implode;
use function is_bool;
use function is_dir;
use function json_decode;
use function mkdir;
use function sprintf;

require_once __DIR__.'/parse-common.php';

if(!file_exists(FILE.'.json')){
	throw new RuntimeException('invalid benchmark report [file_exists()]');
}

$data = file_get_contents(FILE.'.json');

if($data === false){
	throw new RuntimeException('invalid benchmark report [file_get_contents()]');
}

$json = json_decode($data, true);

$htmlHead = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="stylesheet" href="./benchmark.css">
<title>%s results</title>
</head>
<body>';

$htmlFoot = '</body>
</html>';

$html = [];

// General information/overview

$env   = $json['env'];
$suite = $json['suite'];

$html['index'][] = sprintf($htmlHead, 'Benchmark');
$html['index'][] = '<h1>Benchmark results</h1>';
$html['index'][] = '<h2>Environment</h2>';
$html['index'][] = '<table><thead>';
$html['index'][] = '<tr><th>Name</th><th>Value</th></tr>';
$html['index'][] = '</thead><tbody>';

$html['index'][] = sprintf('<tr><td>date</td><td style="text-align: left;">%s %s</td></tr>', $suite['date'], $suite['time']);
$html['index'][] = sprintf(
	'<tr><td>environment</td><td style="text-align: left;">%s %s, %s</td></tr>',
	$env['uname_os'],
	$env['uname_version'],
	$env['uname_machine'],
);
$html['index'][] = sprintf('<tr><td>tag</td><td style="text-align: left;">%s</td></tr>', htmlspecialchars($suite['tag']));

foreach(['php_version', 'php_ini', 'php_extensions', 'php_xdebug', 'opcache_extension_loaded', 'opcache_enabled'] as $field){

	// prettify the boolean values
	if(is_bool($env[$field])){
		$env[$field] = ($env[$field]) ? '✓' : '✗';
	}

	$html['index'][] = sprintf('<tr><td>%s</td><td style="text-align: left;">%s</td></tr>', $field, $env[$field]);
}

$html['index'][] = '</tbody></table>';

// list indiviidual reports
$html['index'][] = '<h2>Reports</h2>';

$list = [];

foreach(array_keys($json['benchmark']) as $benchmark){

	// add a file & header
	$html[$benchmark][] = sprintf($htmlHead, $benchmark);
	$html[$benchmark][] = sprintf('<h1>%s</h1>', $benchmark);
	$html[$benchmark][] = sprintf('<code>%s</code>', $json['benchmark'][$benchmark]['class']);

	foreach(array_keys($json['benchmark'][$benchmark]['subjects']) as $subject){
		// list item
		$list[$benchmark][] = $subject;

		$subj = $json['benchmark'][$benchmark]['subjects'][$subject];

		$html[$benchmark][] = sprintf('<h2 id="%1$s">%1$s</h2>', $subject);
		$html[$benchmark][] = sprintf('<div>Revs: %s, Iterations: %s</div>', $subj['revs'], $subj['iterations']);
		$html[$benchmark][] = parseVariants($subj['variants']);
	}

	// close document
	$html[$benchmark][] = '<a class="return" href="./index.html">back to overview</a>';
	$html[$benchmark][] = $htmlFoot;
}

// create overview list

$html['index'][] = '<ul>';

foreach($list as $benchmark => $subjects){
	// list item
	$html['index'][] = sprintf('<li><a href="./%1$s.html">%1$s</a><ul>', $benchmark);

	foreach($subjects as $subject){
		// list sub-item
		$html['index'][] = sprintf('<li><a href="./%1$s.html#%2$s">%2$s</a></li>', $benchmark, $subject);
	}

	$html['index'][] = '</ul></li>';
}

$html['index'][] = '</ul>';

// close document
$html['index'][] = $htmlFoot;

if(!is_dir(BUILDDIR.'/html/')){
	mkdir(BUILDDIR.'/html/');
}

copy(__DIR__.'/benchmark.css', BUILDDIR.'/html/benchmark.css');

foreach($html as $file => $content){
	file_put_contents(BUILDDIR.'/html/'.$file.'.html', implode("\n", $content));
}
