<?php
namespace Paro\BuildParametersHandler;
use Composer\Script\Event;
use Incenteev\ParameterHandler\Processor;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class ParametersHandler
{
    public static function buildParameters(Event $event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();
        if (!isset($extras['build-parameters'])) {
            throw new \InvalidArgumentException('The parameter handler needs to be configured through the extra.build-parameters setting.');
        }
        $configs = $extras['build-parameters'];
        if (!is_array($configs)) {
            throw new \InvalidArgumentException('The extra.build-parameters setting must be an array or a configuration object.');
        }

	    if (!isset($configs['build-folder'])) {
		    $configs['build-folder'] = 'build';
	    }

	    self::initBuildDirectory($configs['build-folder']);
        self::processFiles($configs, $event);
        self::processIncenteevParameters($configs, $event);
    }

	private static function processIncenteevParameters($configs, $event) {
    	if (!isset($configs['incenteev-parameters'])) {
    		return true;
	    }

		$processor = new Processor($event->getIO());
		$parameters = $configs['incenteev-parameters'];
		if (array_keys($parameters) !== range(0, count($parameters) - 1)) {
			$parameters = array($parameters);
		}

		foreach ($parameters as $config) {
			if (!is_array($config)) {
				throw new \InvalidArgumentException('The extra.build-parameters setting must be an array of configuration objects.');
			}

			$file = self::preparePath($config['file'], $event);

			$config['dist-file'] = $file;
			$config['file'] = $configs['build-folder'] . '/' . (isset($config['name'])? $config['name'] : $file);
			$processor->processFile($config);
			self::processFile($config);
		}
    }

	private static function preparePath($path, $event) {
		if (($env = self::getEnvParameter($event)) !== false) {
			return str_replace("{env}", $env, $path);
		} else {
			return $path;
		}
	}

    private static function getEnvParameter(Event $event) {
    	$arguments = $event->getArguments();
		if (!is_array($arguments)) {
			return false;
		}

	    return array_reduce($arguments, function($ret, $item) {
			if (substr(strtolower($item), 0, 5) == '--env') {
				$val = explode('=', $item);
				return trim($val[1]);
			}
		}, false);

    }

	private static function processFiles($configs, $event) {
		if (!isset($configs['files'])) {
			return true;
		}

		$files = $configs['files'];
		if (array_keys($files) !== range(0, count($files) - 1)) {
			$files = array($files);
		}
		foreach ($files as $file) {
			if (!is_array($file)) {
				throw new \InvalidArgumentException('The extra.files setting must be an array of configuration objects.');
			}

			$path = self::preparePath($file['file'], $event);
			$destination = isset($file['name']) ? $file['name'] : $path;
			copy($path, $configs['build-folder'] . '/' . $destination);
            if (isset($file['name'])) {
                $event->getIO()->write(sprintf('<info>Copying the "%s" into "%s" file</info>', $path, $destination));
            } else {
                $event->getIO()->write(sprintf('<info>Copying the "%s" file</info>', $path));
            }

		}
	}

	private static function initBuildDirectory($dir) {
		if (!is_dir($dir)) {
            mkdir($dir);
        }
	}

    private static function processFile($config)
    {
        $file = $config['file'];
        $yamlParser = new Parser();
        $sourceValues = $yamlParser->parse(file_get_contents($file));
        $values = array_map(function($item) {
            $item = trim($item);
            if (substr(strtolower($item), 0, 5) === "%env(" && substr(strtolower($item), -2) == ')%') {
                $envName = substr(trim($item), 5);
                $envName = substr($envName, 0, strlen($envName) - 2);
                return getenv($envName);
            } else {
                return $item;
            }
        }, $sourceValues['parameters']);

        file_put_contents($file, sprintf("# This file is auto-generated during the build process at %s\n", date(DATE_ATOM)) . Yaml::dump($values, 99));
    }
}