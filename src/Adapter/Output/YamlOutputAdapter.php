<?php

namespace Paro\EnvironmentParameters\Adapter\Output;

use Symfony\Component\Yaml\Yaml;

class YamlOutputAdapter implements OutputAdapterInterface
{
    /**
     * @var string
     */
    private $parameterKey;

    public function __construct($parameterKey)
    {

        $this->parameterKey = $parameterKey;
    }

    public static function getName()
    {
        return 'yaml';
    }

    public function process($parameters, $fileName, $env, $date)
    {
        file_put_contents($fileName, sprintf("# This file is auto-generated during the build process of '%s' environment at %s\n", $env, date(DATE_ATOM, $date)) . Yaml::dump(array($this->parameterKey => $parameters)));
    }

}