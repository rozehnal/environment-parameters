<?php

namespace Paro\BuildParametersHandler;

class FileHandler
{
    /**
     * @var array
     */
    private $arguments;

    /**
     * FileHandler constructor.
     * @param array $arguments
     */
    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    public function resolvePath($currentPath, $importPath) {
        if (substr($importPath, 0, 1) == '/') {
            return $this->preparePath($importPath);
        } else {
            $path = dirname($currentPath) . DIRECTORY_SEPARATOR . $importPath;
            return $this->preparePath($path);
        }
    }

    public function preparePath($path) {
        if (($env = $this->getEnvParameter('env')) !== false) {
            return str_replace("{env}", $env, $path);
        } else {
            return $path;
        }
    }

    public function getEnvParameter($name) {
        return array_reduce($this->arguments, function($ret, $item) use ($name) {
            if (substr(strtolower($item), 0, 5) == '--' . $name) {
                $val = explode('=', $item);
                return trim($val[1]);
            }
        }, false);

    }

    public function initDirectory($dir) {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }
}
