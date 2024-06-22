<?php
/**
 * Auto generates the configuration settings markdown source
 *
 * @created      03.10.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

use chillerlan\QRCode\QROptions;

require_once __DIR__.'/../vendor/autoload.php';

$file    = 'Usage/Configuration-settings.md';
$content = [
	'# Configuration settings',
	'<!-- This file is auto generated from the source of QROptions.php -->',
];

$reflectionClass = new ReflectionClass(QROptions::class);

foreach($reflectionClass->getProperties(ReflectionProperty::IS_PROTECTED) as $reflectionProperty){
	$docblock = $reflectionProperty->getDocComment();

	// don't document deprecated settings
#	if(str_contains($docblock, '@deprecated')){
#		continue;
#	}

	$content[] = sprintf("## %s\n", $reflectionProperty->getName());

	$lines = array_map(fn($l) => trim($l, "\ \t\n\r\0\x0B*"), explode("\n", $docblock));

	array_shift($lines);
	array_pop($lines);

	$see  = [];
	$link = [];

	foreach($lines as $line){

		// skip @todo and @var
		if(str_contains($line, '@todo') || str_contains($line, '@var')){
			continue;
		}

		if(str_contains($line, '@deprecated')){
			$line = str_replace('@deprecated', '**Deprecated:**', $line);
		}

		// collect links for "see also"
		if(str_starts_with($line, '@see')){
			$see[] = substr($line, 5); // cut off the "@see "

			continue;
		}

		// collect links for "links"
		if(str_starts_with($line, '@link')){
			$link[] = substr($line, 6); // cut off the "@link "

			continue;
		}

		$content[] = $line;
	}

	// add a "see also" section
	if(!empty($see)){
		$content[] = "\n**See also:**\n";

		foreach($see as $line){

			// normal links
			if(str_starts_with($line, 'http')){
				$content[] = sprintf('- [%s](%s)', explode('://', $line)[1], $line);
			}
			// php.net documentation
			elseif(str_starts_with($line, '\\') && !str_contains($line, 'chillerlan')){
				$path = str_replace(['\\', '::', '()', '_'], ['', '.', '', '-'], strtolower($line));

				if(!str_contains($line, '::')){
					$path = 'function.'.$path;
				}

				$content[] = sprintf('- [php.net: `%s`](https://www.php.net/manual/%s)', $line, $path);
			}
			// FQCN
			else{
				$content[] = sprintf('- `%s`', $line);
			}

		}

	}

	// add "Links" section
	if(!empty($link)){
		$content[] = "\n**Links:**\n";

		foreach($link as $line){

			// skip non-url
			if(!str_starts_with($line, 'http')){
				continue;
			}

			$url = explode(' ', $line, 2);

			$content[] = match(count($url)){
				1 => sprintf('- [%s](%s)', explode('://', $url[0])[1], $url[0]),
				2 => sprintf('- [%s](%s)', trim($url[1]), $url[0]),
			};

		}

	}

	$content[] = "\n";
}

file_put_contents(__DIR__.'/'.$file, implode("\n", $content));

printf('Built "%s" from "%s"', $file, QROptions::class);

exit(0);
