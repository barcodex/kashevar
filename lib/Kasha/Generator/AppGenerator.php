<?php

namespace Kasha\Generator;

class AppGenerator
{
	private $rootPath = '';

	public function __construct($rootPath)
	{
		$this->rootPath = $rootPath;
		// Try to create root path if it does not exist
		if (!file_exists($rootPath)) {
			mkdir($rootPath);
		}
	}

	public function createApp()
	{
		$this->createFolderIfNotExists('/app');
		$this->createFolderIfNotExists('/app/cache');
		$this->createFolderIfNotExists('/app/modules');
		$this->createFolderIfNotExists('/app/modules.translation');

		$this->copyFileIfNotExists('/app/autoload.php', 'autoload.php');
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
					$code = str_replace($search, $replace, $code);
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
