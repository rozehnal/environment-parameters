<?php

namespace Paro\BuildParametersHandler;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Incenteev\ParameterHandler\Processor;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class IncenteevParametersProcessor
{
    private static $PARAMETER_KEY = null;

    /**
     * @var IOInterface
     */
    private $io;
    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * IncenteevParametersProcessor constructor.
     * @param IOInterface $io
     * @param FileHandler $fileHandler
     */
    public function __construct(IOInterface $io, FileHandler $fileHandler)
    {
        $this->io = $io;
        $this->fileHandler = $fileHandler;
    }

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
                throw new \InvalidArgumentException('The extra.build-parameters setting must be an array of configuration objects.');
            }

            $file = $this->fileHandler->preparePath($config['file'], $event->getArguments());


            $config['dist-file'] = $config['file'];
            $config['file'] = $configs['build-folder'] . '/' . (isset($config['name'])? $config['name'] : $file);
            $this->processFile($config['dist-file'], $config['file']);

            $config['dist-file'] = $config['file'];
            $processor->processFile($config);
        }
    }

    public function processFile($inFile, $outFile = null)
    {
        $yamlParser = new Parser();
        $values = $yamlParser->parse(file_get_contents($inFile));

        $values[self::$PARAMETER_KEY] = $this->procesEnvironmentalVariables($values[self::$PARAMETER_KEY]);

        if (isset($values['imports']) && is_array($values['imports'])) {
            foreach ($values['imports'] as $importFile) {
                $parametersFromFile = $this->processFile(dirname($inFile) . DIRECTORY_SEPARATOR . $importFile['resource']);
                $values = array_replace_recursive($parametersFromFile, $values);
            }
            unset($values['imports']);
        }

        if (!is_null($outFile)) {
            file_put_contents($outFile, Yaml::dump($values), 99);
        } else {
            return $values;
        }
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