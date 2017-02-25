<?php

namespace Paro\BuildParametersHandler;

use Composer\Script\Event;
use Incenteev\ParameterHandler\Processor;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class IncenteevParametersProcessor
{
    private static $PARAMETER_KEY = null;

    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * IncenteevParametersProcessor constructor.
     * @param FileHandler $fileHandler
     */
    public function __construct(FileHandler $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    /**
     * @param $configs
     * @param Event $event
     * @return bool
     */
    public function process($configs, Event $event) {
        if (!isset($configs['incenteev-parameters'])) {
            return true;
        }

        $processor = new Processor($event->getIO());
        $parameters = $configs['incenteev-parameters'];
        if (array_keys($parameters) !== range(0, count($parameters) - 1)) {
            $parameters = array($parameters);
        }

        if (empty($parameters['parameter-key'])) {
            self::$PARAMETER_KEY = 'parameters';
        } else {
            self::$PARAMETER_KEY = $parameters['parameter-key'];
        }

        foreach ($parameters as $config) {
            if (!is_array($config)) {
                throw new \InvalidArgumentException('The extra.environment-parameters.incenteev-parameters setting must be an array of configuration objects.');
            }

            $file = $this->fileHandler->preparePath($config['file']);


            $config['dist-file'] = $file;
            $config['file'] = $this->fileHandler->preparePath($configs['build-folder'] . '/' . (isset($config['name'])? $config['name'] : $file));
            $this->processFile($config['dist-file'], $config['file']);

            $config['dist-file'] = $config['file'];
            $processor->processFile($config);

            $this->updateCommentInFile($config['file']);
        }

        return true;
    }

    /**
     * @param $inFile
     * @param null $outFile
     * @return array|bool|mixed
     */
    public function processFile($inFile, $outFile = null)
    {
        $yamlParser = new Parser();
        $values = $yamlParser->parse(file_get_contents($inFile));

        $values[self::$PARAMETER_KEY] = $this->procesEnvironmentalVariables($values[self::$PARAMETER_KEY]);

        if (isset($values['imports']) && is_array($values['imports'])) {
            foreach ($values['imports'] as $importFile) {
                $parametersFromFile = $this->processFile($this->fileHandler->resolvePath($inFile, $importFile['resource']));
                $values = array_replace_recursive($parametersFromFile, $values);
            }
            unset($values['imports']);
        }

        if (!is_null($outFile)) {
            file_put_contents($outFile, Yaml::dump($values), 99);
        } else {
            return $values;
        }

        return true;
    }

    protected function updateCommentInFile($file)
    {
        $yamlParser = new Parser();
        $values = $yamlParser->parse(file_get_contents($file));
        file_put_contents($file, sprintf("# This file is auto-generated during the build process at %s\n", date(DATE_ATOM)) . Yaml::dump($values), 99);
    }

    protected function procesEnvironmentalVariables(array $parameters)
    {
        return array_map(function($item) {
            $item = trim($item);
            if (substr(strtolower($item), 0, 5) === "%env(" && substr(strtolower($item), -2) == ')%') {
                $envName = substr(trim($item), 5);
                $envName = substr($envName, 0, strlen($envName) - 2);
                return getenv($envName);
            } else {
                return $item;
            }
        }, $parameters);
    }
}
