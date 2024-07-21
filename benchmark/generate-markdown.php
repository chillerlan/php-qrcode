<?php
/**
 * Generates a benchmark report in Markdown
 *
 * @created      27.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeBenchmark;

use RuntimeException;
use function array_keys;
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
use function strtolower;

require_once __DIR__.'/parse-common.php';

if(!file_exists(FILE.'.json')){
	throw new RuntimeException('invalid benchmark report [file_exists()]');
}

$data = file_get_contents(FILE.'.json');

if($data === false){
	throw new RuntimeException('invalid benchmark report [file_get_contents()]');
}

$json     = json_decode($data, true);
$markdown = [];

// General information/overview

$env   = $json['env'];
$suite = $json['suite'];

$markdown['Readme'][] = "# Benchmark results\n";
$markdown['Readme'][] = "## Environment\n";
$markdown['Readme'][] = '| Name | Value |';
$markdown['Readme'][] = '|------|-------|';
$markdown['Readme'][] = sprintf('| date | %s %s |', $suite['date'], $suite['time']);
$markdown['Readme'][] = sprintf('| environment | %s %s, %s |', $env['uname_os'], $env['uname_version'], $env['uname_machine']);
$markdown['Readme'][] = sprintf('| tag | %s |', htmlspecialchars($suite['tag']));


foreach(['php_version', 'php_ini', 'php_extensions', 'php_xdebug', 'opcache_extension_loaded', 'opcache_enabled'] as $field){

	// prettify the boolean values
	if(is_bool($env[$field])){
		$env[$field] = ($env[$field]) ? '✓' : '✗';
	}

	$markdown['Readme'][] = sprintf('| %s | %s |', $field, $env[$field]);
}

// list indiviidual reports
$markdown['Readme'][] = '';
$markdown['Readme'][] = '## Reports';
$markdown['Readme'][] = '';

$list = [];

foreach(array_keys($json['benchmark']) as $benchmark){
	// add a file & header
	$markdown[$benchmark][] = sprintf("# %s\n", $benchmark);
	$markdown[$benchmark][] = sprintf("`%s`\n", $json['benchmark'][$benchmark]['class']);

	foreach(array_keys($json['benchmark'][$benchmark]['subjects']) as $subject){
		// list item
		$list[$benchmark][] = $subject;

		$subj = $json['benchmark'][$benchmark]['subjects'][$subject];

		$markdown[$benchmark][] = sprintf("## %s\n", $subject);
		$markdown[$benchmark][] = sprintf("**Revs: %s, Iterations: %s**\n", $subj['revs'], $subj['iterations']);
		$markdown[$benchmark][] = parseVariants($subj['variants']);
		$markdown[$benchmark][] = '';
	}

	$markdown[$benchmark][] = '[back to overview](./Benchmark.md)';
}

// create overview list
foreach($list as $benchmark => $subjects){
	// list item
	$markdown['Readme'][] = sprintf('- [%1$s](./%1$s.md)', $benchmark);

	foreach($subjects as $subject){
		// list sub-item
		$markdown['Readme'][] = sprintf('  - [%2$s](./%1$s.md#%3$s)', $benchmark, $subject, strtolower($subject));
	}
}


if(!is_dir(BUILDDIR.'/markdown/')){
	mkdir(BUILDDIR.'/markdown/');
}

foreach($markdown as $file => $content){
	file_put_contents(BUILDDIR.'/markdown/'.$file.'.md', implode("\n", $content));
}
