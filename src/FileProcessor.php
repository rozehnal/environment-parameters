<?php

namespace Paro\BuildParametersHandler;

use Composer\IO\IOInterface;
use Composer\Script\Event;

class FileProcessor
{

    /**
     * @var IOInterface
     */
    private $io;
    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * FileProcessor constructor.
     * @param IOInterface $io
     * @param FileHandler $fileHandler
     */
    public function __construct(IOInterface $io, FileHandler $fileHandler)
    {
        $this->io = $io;
        $this->fileHandler = $fileHandler;
    }

    /**
     * @param $configs
     * @return bool
     */
    public function process($configs)
    {
        if (!isset($configs['files'])) {
            return true;
        }

        $files = $configs['files'];
        if (array_keys($files) !== range(0, count($files) - 1)) {
            $files = array($files);
        }
        foreach ($files as $file) {
            if (!is_array($file)) {
                throw new \InvalidArgumentException('The extra.files setting must be an array of configuration objects.');
            }

            $path = $this->fileHandler->preparePath($file['file']);
            $destination = $configs['build-folder'] . '/' . (isset($file['name']) ? $file['name'] : $path);
            copy($path, $destination);
            if (isset($file['name'])) {
                $this->io->write(sprintf('<info>Copying the "%s" into "%s" file</info>', $path, $destination));
            } else {
                $this->io->write(sprintf('<info>Copying the "%s" file</info>', $path));
            }

        }
        return true;
    }

}
