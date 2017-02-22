<?php
namespace Paro\BuildParametersHandler;
use Composer\Script\Event;
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

        $processor = new \Incenteev\ParameterHandler\Processor($event->getIO());

        $parameters = $configs['incenteev-parameters'];
        if (array_keys($parameters) !== range(0, count($parameters) - 1)) {
            $parameters = array($parameters);
        }

        foreach ($parameters as $config) {
            if (!is_array($config)) {
                throw new \InvalidArgumentException('The extra.build-parameters setting must be an array of configuration objects.');
            }

            $config['dist-file'] = $config['file'];
            $config['file'] = $configs['build-folder'] . '/' . $config['file'];
            $processor->processFile($config);
            self::updateComment($config['file']);


        }
    }

    private static function updateComment($file)
    {
        $yamlParser = new Parser();
        $values = $yamlParser->parse(file_get_contents($file));
        file_put_contents($file, sprintf("# This file is auto-generated during the build process at %s\n", date(DATE_ATOM)) . Yaml::dump($values, 99));
    }
}