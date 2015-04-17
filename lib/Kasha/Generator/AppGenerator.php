<?php

namespace Kasha\Generator;

use Temple\Util;

class AppGenerator
{
	private $fp = null;
	private $rootPath = '';
	private $config = array();

	public function __construct($rootPath)
	{
		$this->rootPath = $rootPath;
		// Try to create root path if it does not exist
		if (!file_exists($rootPath)) {
			mkdir($rootPath);
		}
		// open STDIN to get more values interactively
		$this->fp = fopen("php://stdin", "r");
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

	private function prompt($message, $default = '')
	{
		$output = $default;
		do {
			print $message . ($default != '' ? " ($default) " : ' ');
			$answer = trim(fgets($this->fp));
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

		$this->copyFileIfNotExists('/app/autoload.php', 'autoload.php');

		$replacements = $this->config;
		$replacements['APP_NAME'] = $this->getAppName();
		$namespace = Util::lavnn('APP_NAMESPACE', $replacements, '');
		$replacements['APP_NAMESPACE'] = $this->prompt('Project namespace?', $namespace);
		$description = Util::lavnn('DESCRIPTION', $replacements, '');
		$replacements['DESCRIPTION'] = $this->prompt('Project description?', $description);
		$description = Util::lavnn('WEBSITE', $replacements, '');
		$replacements['WEBSITE'] = $this->prompt('Website URL?', $description);
		$description = Util::lavnn('LICENSE', $replacements, '');
		$replacements['LICENSE'] = $this->prompt('License?', $description);
		$description = Util::lavnn('AUTHOR_NAME', $replacements, '');
		$replacements['AUTHOR_NAME'] = $this->prompt('Author name?', $description);
		$description = Util::lavnn('AUTHOR_EMAIL', $replacements, '');
		$replacements['AUTHOR_EMAIL'] = $this->prompt('Author email?', $description);
		$this->copyFileIfNotExists('/composer.json', 'composer.json', $replacements);

		print 'Create modules? (Y/n)';
		$answer = strtoupper(trim(fgets($this->fp)));
		if ($answer == 'Y' || $answer == '') {
			do {
				print 'module name (provide no name to quit): ';
				$moduleName = trim(fgets($this->fp));
				if ($moduleName != '') {
					$this->createModule($moduleName);
				}
				print PHP_EOL;
			} while ($moduleName != '');
		}

	}

	public function createModuleStructure($moduleName)
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

	public function createModule($moduleName)
	{
		$this->createModuleStructure($moduleName);

		print 'Create actions? (Y/n)';
		$answer = strtoupper(trim(fgets($this->fp)));
		if ($answer == 'Y' || $answer == '') {
			do {
				print 'action name (provide no name to quit): ';
				$actionName = trim(fgets($this->fp));
				if ($actionName != '') {
					print 'action type; p (page) / f (form) / i (inline) / j (JSON): ';
					$actionType = strtolower(trim(fgets($this->fp)));
					if (in_array($actionType, ['p', 'f', 'i', 'j'])) {
						$this->createAction($moduleName, $actionName, array('type' => $actionType));
					}
					print PHP_EOL;
				}
			} while ($actionName != '');
		}
	}

	public function createAction($moduleName, $actionName, $params)
	{
		if ($moduleName != '' && $actionName != '') {
			$this->createModuleStructure($moduleName);
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
		$replacements = array('MODULE' => $moduleName, 'NAME' => $actionName);
		$this->copyFileIfNotExists($fileName, 'page.php');
		// main template
		$fileName = '/app/modules/' . $moduleName . '/templates/' . $actionName . '.html';
		$this->copyFileIfNotExists($fileName, 'page.html');
		// title template
		$fileName = '/app/modules/' . $moduleName . '/templates/' . $actionName . '.title.html';
		$this->writeFile($fileName, '[[]]');
		// js template
		$fileName = '/app/modules/' . $moduleName . '/templates/' . $actionName . '.js.html';
		$this->copyFileIfNotExists($fileName, 'page.js.html');
	}

	private function createFormActionFiles($moduleName, $actionName)
	{
		// action itself
		$fileName = '/app/modules/' . $moduleName . '/actions/' . $actionName . '.php';
		$replacements = array('MODULE' => $moduleName, 'NAME' => $actionName);
		$this->copyFileIfNotExists($fileName, 'form.php', $replacements);
		// flash/error messages for success/failure
		$fileName = '/app/modules/' . $moduleName . '/templates/flash.' . $actionName . '.success.html';
		$this->copyFileIfNotExists($fileName, 'form.flash.html');
		$fileName = '/app/modules/' . $moduleName . '/templates/flash.' . $actionName . '.success.html';
		$this->copyFileIfNotExists($fileName, 'form.error.html');
	}

	private function createInlineActionFiles($moduleName, $actionName)
	{
		// action itself
		$fileName = '/app/modules/' . $moduleName . '/actions/' . $actionName . '.php';
		$replacements = array('MODULE' => $moduleName, 'NAME' => $actionName);
		$this->copyFileIfNotExists($fileName, 'inline.php', $replacements);
	}

	private function createJsonActionFiles($moduleName, $actionName)
	{
		// action itself
		$fileName = '/app/modules/' . $moduleName . '/actions/' . $actionName . '.php';
		$replacements = array('MODULE' => $moduleName, 'NAME' => $actionName);
		$this->copyFileIfNotExists($fileName, 'json.php', $replacements);
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
