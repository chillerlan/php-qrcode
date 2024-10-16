<?php
/**
 * BuildDirTrait.php
 *
 * @created      07.01.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest;

use RuntimeException;
use function dirname, file_exists, file_get_contents, is_file, mkdir, realpath, sprintf, trim;

/**
 * Trait BuildDirTrait
 */
trait BuildDirTrait{

	private string $_buildDir = __DIR__.'/../.build/';

	/**
	 * returns the full raw path to the build dir
	 */
	protected function getBuildPath(string $subPath):string{
		return $this->_buildDir.trim($subPath, '\\/');
	}

	/**
	 * attempts to create the build dir
	 *
	 * @throws \RuntimeException
	 */
	protected function createBuildDir(string $subPath):void{
		$dir = $this->getBuildPath($subPath);

		// attempt to write
		if(!file_exists($dir)){
			$created = mkdir($dir, 0777, true);

			if(!$created){
				throw new RuntimeException('could not create build dir');
			}
		}
	}

	/**
	 * returns the full (real) path to the given build path
	 *
	 * @throws \RuntimeException
	 */
	protected function getBuildDir(string $subPath = ''):string{
		$dir = realpath($this->getBuildPath($subPath));

		if(empty($dir)){
			throw new RuntimeException('invalid build dir');
		}

		return dirname($dir);
	}

	/**
	 * returns the full (real) path to the given build file
	 *
	 * @throws \RuntimeException
	 */
	protected function getBuildFilePath(string $fileSubPath):string{
		$file = realpath($this->getBuildPath($fileSubPath));

		if(empty($file)){
			throw new RuntimeException('invalid build dir/file');
		}

		if(!is_file($file)){
			throw new RuntimeException(sprintf('the given path "%s" found in "%s" is not a file', $fileSubPath, $file));
		}

		return $file;
	}

	/**
	 * returns the contents of the given build file
	 *
	 * @throws \RuntimeException
	 */
	protected function getBuildFileContent(string $fileSubPath):string{
		$content = file_get_contents($this->getBuildFilePath($fileSubPath));

		if($content === false){
			throw new RuntimeException('file_get_contents() error while reading build file');
		}

		return $content;
	}

}
