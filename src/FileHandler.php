<?php

namespace Paro\BuildParametersHandler;

class FileHandler
{
    public function preparePath($path, array $arguments) {
        if (($env = $this->getEnvParameter($arguments, 'env')) !== false) {
            return str_replace("{env}", $env, $path);
        } else {
            return $path;
        }
    }

    public function getEnvParameter(array $arguments, $name) {
        return array_reduce($arguments, function($ret, $item) use ($name) {
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