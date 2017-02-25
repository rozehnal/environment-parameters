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
        if (substr($importPath, 0, 1) == DIRECTORY_SEPARATOR) {
            return $this->preparePath($importPath);
        } else {
            $path = dirname($currentPath) . DIRECTORY_SEPARATOR . $importPath;
            return $this->preparePath($path);
        }
    }

    public function preparePath($path) {
        if (($env = $this->getArgumentValue('env')) !== false) {
            return str_replace("{env}", $env, $path);
        } else {
            return $path;
        }
    }

    public function getArgumentValue($name) {
        return array_reduce($this->arguments, function($ret, $item) use ($name) {
            if (substr(strtolower($item), 0, strlen($name)+2) == '--' . $name) {
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
