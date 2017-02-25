<?php

namespace Paro\BuildParametersHandler;

use Composer\IO\IOInterface;
use Symfony\Component\Filesystem\Filesystem;

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
     * @var Filesystem
     */
    private $fs;

    /**
     * FileProcessor constructor.
     * @param Filesystem $fs
     * @param IOInterface $io
     * @param FileHandler $fileHandler
     */
    public function __construct(Filesystem $fs, IOInterface $io, FileHandler $fileHandler)
    {
        $this->fs = $fs;
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
                throw new \InvalidArgumentException('The extra.environment-parameters.files setting must be an array.');
            }

            $path = $this->fileHandler->preparePath($file['file']);
            $destination = $configs['build-folder'] . '/' . (isset($file['name']) ? $file['name'] : $path);
            $this->fs->copy($path, $destination, true);
            if (isset($file['name'])) {
                $this->io->write(sprintf('<info>Copying the "%s" into "%s" file</info>', $path, $destination));
            } else {
                $this->io->write(sprintf('<info>Copying the "%s" file</info>', $path));
            }

        }
        return true;
    }

}
