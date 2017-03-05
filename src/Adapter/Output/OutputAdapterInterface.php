<?php
namespace Paro\EnvironmentParameters\Adapter\Output;

interface OutputAdapterInterface
{
    /**
     * @return string
     */
    public static function getName();

    /**
     * @param $parameters
     * @param $fileName
     * @param $env
     */
    public function process($parameters, $fileName, $env);
}