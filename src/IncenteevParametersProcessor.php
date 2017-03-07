<?php

namespace Paro\EnvironmentParameters;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Incenteev\ParameterHandler\Processor;
use Paro\EnvironmentParameters\Adapter\Output\PHPConstantsOutputAdapter;
use Paro\EnvironmentParameters\Adapter\Output\YamlOutputAdapter;
use Symfony\Component\Filesystem\Filesystem;
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
     * @var IOInterface
     */
    private $io;
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * IncenteevParametersProcessor constructor.
     * @param Filesystem $fs
     * @param FileHandler $fileHandler
     * @param IOInterface $io
     */
    public function __construct(Filesystem $fs, FileHandler $fileHandler, IOInterface $io)
    {
        $this->fileHandler = $fileHandler;
        $this->io = $io;
        $this->fs = $fs;
    }

    /**
     * @param $configs
     * @param Event $event
     * @return bool
     */
    public function process($configs, Event $event)
    {
        $this->fs->remove($configs['build-folder']);

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

            $file = $this->fileHandler->findFile($config['file']);

            $outputFileName = $this->fileHandler->preparePath($configs['build-folder'] . '/' . (isset($config['name']) ? $config['name'] : $file));
            $this->processFile($file, $outputFileName . '.dist');

            $config['dist-file'] = $outputFileName . '.dist';
            $config['file'] = $outputFileName;
            $processor->processFile($config);
            $this->fs->remove($outputFileName . '.dist');

            $outputFormat = isset($config['output-format']) ? $config['output-format'] : YamlOutputAdapter::getName();
            $this->writeOutputFile($outputFileName, $outputFormat);
        }

        return true;
    }

    /**
     * @param $inFile
     * @param null $outFile
     * @param array $stack
     * @return array|bool|mixed
     */
    public function processFile($inFile, $outFile = null, array $stack = array())
    {
        $yamlParser = new Parser();
        $values = $yamlParser->parse(file_get_contents($this->fileHandler->findFile($inFile)));

        $values[self::$PARAMETER_KEY] = $this->processEnvironmentalVariables($values[self::$PARAMETER_KEY]);

        if (isset($values['imports']) && is_array($values['imports'])) {
            foreach ($values['imports'] as $importFile) {
                $filePath = $this->fileHandler->resolvePath($inFile, $importFile['resource']);
                $filePathFull = realpath($filePath);
                if (in_array($filePathFull, $stack)) {
                    $this->io->write(sprintf('<error>Skipping cyclic import in "%s" of the "%s" file</error>', $inFile, $filePath));
                    continue;
                }
                $stack[] = $filePathFull;
                $parametersFromFile = $this->processFile($filePath, null, $stack);
                $values = array_replace_recursive($parametersFromFile, $values);
            }
            unset($values['imports']);
        }

        if (!is_null($outFile)) {
	        ksort($values[self::$PARAMETER_KEY]);
            $this->fs->dumpFile($outFile, Yaml::dump($values));
        } else {
            return $values;
        }

        return true;
    }

    /**
     * @param $file
     * @param $outputFormat
     * @return mixed
     */
    protected function writeOutputFile($file, $outputFormat)
    {
        $yamlParser = new Parser();
        $values = $yamlParser->parse(file_get_contents($file));
        $values = $values[self::$PARAMETER_KEY];
        $env = $this->fileHandler->getArgumentValue('env');
        $outputFormat = strtolower($outputFormat);

        $supportedAdapters = array(
            new YamlOutputAdapter(self::$PARAMETER_KEY),
            new PHPConstantsOutputAdapter(),
        );

        foreach ($supportedAdapters as $adapter) {
            if (strtolower($adapter->getName()) === $outputFormat) {
                return $adapter->process($values, $file, $env);
            }
        }

        throw new \InvalidArgumentException(sprintf('Adapter "%s" doesn\'t exist', $outputFormat));
    }

    protected function processEnvironmentalVariables(array $parameters)
    {
    	$processed = array();
    	foreach ($parameters as $key => $parameter) {
		    if (!is_string($parameter)) {
			    $processed[$key] = $parameter;
		    } else {
			    $processed[$key] = $this->fileHandler->processEnvironmentalVariable($parameter);
		    }
	    }
	    return $processed;
    }
}
