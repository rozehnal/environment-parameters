<?php

namespace Paro\BuildParametersHandler;

use Composer\Script\Event;

class ParametersHandler
{
    public static function buildParameters(Event $event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();
        if (!isset($extras['environment-parameters'])) {
            throw new \InvalidArgumentException('The parameter handler needs to be configured through the extra.environment-parameters setting.');
        }
        $configs = $extras['environment-parameters'];
        if (!is_array($configs)) {
            throw new \InvalidArgumentException('The extra.environment-parameters setting must be an array or a configuration object.');
        }

	    $fileHandler = new FileHandler($event->getArguments());

        if (!isset($configs['build-folder'])) {
            $configs['build-folder'] = 'build';
        }
        $fileHandler->initDirectory($configs['build-folder']);

	    $fileProcessor = new FileProcessor($event->getIO(), $fileHandler);
        $fileProcessor->process($configs);

        $incenteevProcessor = new IncenteevParametersProcessor($fileHandler);
        $incenteevProcessor->process($configs, $event);
    }
}
