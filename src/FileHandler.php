<?php

namespace Paro\EnvironmentParameters;

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
        if ($this->fs->isAbsolutePath($importPath)) {
            return $this->preparePath($importPath);
        } else {
            $path = dirname($currentPath) . DIRECTORY_SEPARATOR . $importPath;
            if (substr($path, 0, 4) == '././') {
                $path = substr($path, 2);
            }
            return $this->preparePath($path);
        }
    }

    /**
     * @param $path
     * @param null $env
     * @return mixed
     */
    public function preparePath($path, $env = null)
    {
        if (is_null($env)) {
            $env = $this->getArgumentValue('env');
        }

        if ($env !== false) {
            return str_replace('{env}', $env, $path);
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

    public function findFile($path)
    {
        $env = $this->getArgumentValue('env');
        if ($env !== false && strpos($env, DIRECTORY_SEPARATOR) > 0) {
            $envParts = explode(DIRECTORY_SEPARATOR, $env);
            while (count($envParts) > 0) {
                $fileName = $this->preparePath($path, join(DIRECTORY_SEPARATOR, $envParts));
                if ($this->fs->exists($fileName)) {
                    return $fileName;
                }
                unset($envParts[count($envParts) - 1]);
            }
            //root folder
            $fileName = str_replace('{env}/', '', $path);
            if ($this->fs->exists($fileName)) {
                return $fileName;
            }
        } else {
            $fileName = $this->preparePath($path);
            if ($this->fs->exists($fileName)) {
                return $fileName;
            }
        }

        throw new \InvalidArgumentException(sprintf('File "%s" for environment "%s" doesn\'t exists', $path, $env));
    }
}
