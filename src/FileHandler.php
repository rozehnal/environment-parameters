<?php

namespace Paro\BuildParametersHandler;

use Symfony\Component\Filesystem\Filesystem;

class FileHandler
{
    /**
     * @var array
     */
    private $arguments;
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * FileHandler constructor.
     * @param Filesystem $fs
     * @param array $arguments
     */
    public function __construct(Filesystem $fs, array $arguments)
    {
        $this->fs = $fs;
        $this->arguments = $arguments;
    }

    public function resolvePath($currentPath, $importPath)
    {
        if (substr($importPath, 0, 1) == DIRECTORY_SEPARATOR) {
            return $this->preparePath($importPath);
        } else {
            $path = dirname($currentPath) . DIRECTORY_SEPARATOR . $importPath;
            return $this->preparePath($path);
        }
    }

    public function preparePath($path)
    {
        if (($env = $this->getArgumentValue('env')) !== false) {
            return str_replace("{env}", $env, $path);
        } else {
            return $path;
        }
    }

    /**
     * @param $name
     * @return string|false
     */
    public function getArgumentValue($name)
    {
        return array_reduce($this->arguments, function ($carry, $item) use ($name) {
            if (substr(strtolower($item), 0, strlen($name) + 2) == '--' . $name) {
                $val = explode('=', $item);
                return trim($val[1]);
            } else {
                return $carry;
            }
        }, false);

    }

    public function initDirectory($dir)
    {
        if (!$this->fs->exists($dir)) {
            $this->fs->mkdir($dir);
        }
    }
}
