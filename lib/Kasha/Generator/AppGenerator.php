<?php

namespace Kasha\Generator;

use Temple\Util;

class AppGenerator
{
	private $rootPath = '';
	private $config = array();

	public function __construct($rootPath)
	{
		$this->rootPath = $rootPath;
		// Try to create root path if it does not exist
		if (!file_exists($rootPath)) {
			mkdir($rootPath);
		}
	}

	public function setConfig($filePath)
	{
		if (file_exists($filePath)) {
			$this->config = json_decode(file_get_contents($filePath), true);
		}
	}

	public function getAppName()
	{
		return basename($this->rootPath);
	}

	private function prompt($fp, $message, $default = '')
	{
		$output = $default;
		do {
			print $message . ($default != '' ? " ($default) " : '');
			$answer = trim(fgets($fp));
			if ($answer != '') {
				$output = $answer;
			}
		} while ($output == '');

		return $output;
	}

	public function createApp()
	{
		$this->createFolderIfNotExists('/app');
		$this->createFolderIfNotExists('/app/cache');
		$this->createFolderIfNotExists('/app/modules');
		$this->createFolderIfNotExists('/app/modules.translation');

		// open STDIN to get more values interactively
		$fp = fopen("php://stdin", "r");

		$this->copyFileIfNotExists('/app/autoload.php', 'autoload.php');

		$replacements = $this->config;
		$replacements['APP_NAME'] = $this->getAppName();
		$namespace = Util::lavnn('APP_NAMESPACE', $replacements, '');
		$replacements['APP_NAMESPACE'] = $this->prompt($fp, 'Project namespace?', $namespace);
		$description = Util::lavnn('DESCRIPTION', $replacements, '');
		$replacements['DESCRIPTION'] = $this->prompt($fp, 'Project description?', $description);
		$description = Util::lavnn('WEBSITE', $replacements, '');
		$replacements['WEBSITE'] = $this->prompt($fp, 'Website URL?', $description);
		$description = Util::lavnn('LICENSE', $replacements, '');
		$replacements['LICENSE'] = $this->prompt($fp, 'License?', $description);
		$description = Util::lavnn('AUTHOR_NAME', $replacements, '');
		$replacements['AUTHOR_NAME'] = $this->prompt($fp, 'Author name?', $description);
		$description = Util::lavnn('AUTHOR_EMAIL', $replacements, '');
		$replacements['AUTHOR_EMAIL'] = $this->prompt($fp, 'Author email?', $description);
		$this->copyFileIfNotExists('/composer.json', 'composer.json', $replacements);

		print 'Create modules? (Y/n)';
		$answer = strtoupper(trim(fgets($fp)));
		if ($answer == 'Y' || $answer == '') {
			do {
				print 'module name (provide no name to quit): ';
				$moduleName = trim(fgets($fp));
				if ($moduleName != '') {
					$this->createModule($moduleName);
				}
			} while ($moduleName != '');
		}

	}

	public function createModule($moduleName)
	{
		if ($moduleName != '') {
			$this->createFolderIfNotExists('/app');
			$this->createFolderIfNotExists('/app/modules');
			$this->createFolderIfNotExists('/app/modules/' . $moduleName);
			$this->createFolderIfNotExists('/app/modules/' . $moduleName . '/actions');
			$this->createFolderIfNotExists('/app/modules/' . $moduleName . '/templates');
			$this->createFolderIfNotExists('/app/modules/' . $moduleName . '/sql');
		}
	}

	public function createAction($moduleName, $actionName, $params)
	{
		if ($moduleName != '' && $actionName != '') {
			// first, make sure that module exists
			$this->createModule($moduleName);
			// @TODO create action file and related files, depending on type
			if (array_key_exists('type', $params)) {
				switch ($params['type']) {
					case 'p':
						$this->createPageActionFiles($moduleName, $actionName);
						break;
					case 'f':
						$this->createFormActionFiles($moduleName, $actionName);
						break;
					case 'i':
						$this->createInlineActionFiles($moduleName, $actionName);
						break;
					case 'j':
						$this->createJsonActionFiles($moduleName, $actionName);
						break;
				}
			}
		}
	}

	private function createPageActionFiles($moduleName, $actionName)
	{
		// action itself
		$fileName = '/app/modules/' . $moduleName . '/actions/' . $actionName . '.php';
		$code = file_get_contents(__DIR__ . '/Templates/page.php');
		$code = str_replace('%MODULE%', $moduleName, $code);
		$code = str_replace('%NAME%', $actionName, $code);
		$this->writeFile($fileName, $code);
		// main template
		$fileName = '/app/modules/' . $moduleName . '/templates/' . $actionName . '.html';
		$code = file_get_contents(__DIR__ . '/Templates/page.html');
		$this->writeFile($fileName, $code);
		// title template
		$fileName = '/app/modules/' . $moduleName . '/templates/' . $actionName . '.title.html';
		$this->writeFile($fileName, '[[]]');
		// js template
		$fileName = '/app/modules/' . $moduleName . '/templates/' . $actionName . '.js.html';
		$code = file_get_contents(__DIR__ . '/Templates/page.js.html');
		$this->writeFile($fileName, $code);
	}

	private function createFormActionFiles($moduleName, $actionName)
	{
		// action itself
		$fileName = '/app/modules/' . $moduleName . '/actions/' . $actionName . '.php';
		$code = file_get_contents(__DIR__ . '/Templates/form.php');
		$code = str_replace('%MODULE%', $moduleName, $code);
		$code = str_replace('%NAME%', $actionName, $code);
		$this->writeFile($fileName, $code);
		// flash/error messages for success/failure
		$fileName = '/app/modules/' . $moduleName . '/templates/flash.' . $actionName . '.success.html';
		$code = file_get_contents(__DIR__ . '/Templates/form.flash.html');
		$this->writeFile($fileName, $code);
		$fileName = '/app/modules/' . $moduleName . '/templates/error.' . $actionName . '.failure.html';
		$code = file_get_contents(__DIR__ . '/Templates/form.error.html');
		$this->writeFile($fileName, $code);
	}

	private function createInlineActionFiles($moduleName, $actionName)
	{
		// action itself
		$fileName = '/app/modules/' . $moduleName . '/actions/' . $actionName . '.php';
		$code = file_get_contents(__DIR__ . '/Templates/inline.php');
		$code = str_replace('%MODULE%', $moduleName, $code);
		$code = str_replace('%NAME%', $actionName, $code);
		$this->writeFile($fileName, $code);
	}

	private function createJsonActionFiles($moduleName, $actionName)
	{
		// action itself
		$fileName = '/app/modules/' . $moduleName . '/actions/' . $actionName . '.php';
		$code = file_get_contents(__DIR__ . '/Templates/json.php');
		$code = str_replace('%MODULE%', $moduleName, $code);
		$code = str_replace('%NAME%', $actionName, $code);
		$this->writeFile($fileName, $code);
	}

	private function createFolderIfNotExists($folderPath)
	{
		if (!file_exists($this->rootPath . $folderPath)) {
			mkdir($this->rootPath . $folderPath);
		}
	}

	private function copyFileIfNotExists($fileName, $templateName, $replacements = array())
	{
		if (!file_exists($this->rootPath . $fileName)) {
			$code = file_get_contents(__DIR__ . '/Templates/' . $templateName);
			if (count($replacements)) {
				foreach($replacements as $search => $replace) {
					$code = str_replace('%' . $search . '%', $replace, $code);
				}
			}
			$this->writeFile($fileName, $code);
		}
	}

	private function writeFile($filePath, $fileContents)
	{
		file_put_contents($this->rootPath . $filePath, $fileContents);
	}
}
