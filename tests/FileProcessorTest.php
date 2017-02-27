<?php
namespace Paro\EnvironmentParameters\Tests;

use Paro\EnvironmentParameters\FileProcessor;

class FileProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider processProvider
     * @param $env
     * @param $configs
     * @param $expected
     */
    public function testProcess($env, $configs, $expected)
    {
        $fs = $this->getMockBuilder('Symfony\\Component\\Filesystem\\Filesystem')
            ->setMethods(array('copy', 'exists'))
            ->getMock();

        $fs->expects($this->once())
            ->method('copy')
            ->with($this->equalTo($expected['source']), $this->equalTo($expected['destination']))
            ->willReturn(true);

        $fs->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $fileHandler = $this->getMockBuilder('Paro\\EnvironmentParameters\\FileHandler')
            ->setConstructorArgs(array($fs, array()))
            ->setMethods(array('getArgumentValue'))
            ->getMock();

        $fileHandler->expects($this->atLeastOnce())
            ->method('getArgumentValue')
            ->with($this->equalTo('env'))
            ->willReturn($env);

        $io = $this->getMockBuilder('Composer\\IO\\NullIO')
            ->disableOriginalConstructor()
            ->setMethods(array('write'))
            ->getMock();

        $io->expects($this->once())
            ->method('write');

        $fileProcessor = new FileProcessor($fs, $io, $fileHandler);

        $fileProcessor->process($configs);
    }

    public function processProvider()
    {
        return array(
            'simple config with name' => array(
                'env' => 'prod',
                'configs' => array(
                    'build-folder' => 'build',
                    'files' => array(
                        'file' => 'key.{env}.p12',
                        'name' => 'key.p12',
                    ),
                ),
                'expected' => array(
                    'source' => 'key.prod.p12',
                    'destination' => 'build/key.p12'
                )
            ),
            'simple config without name' => array(
                'env' => 'dev',
                'configs' => array(
                    'build-folder' => 'builder',
                    'files' => array(
                        array(
                            'file' => 'key.{env}.p12',
                        )
                    ),
                ),
                'expected' => array(
                    'source' => 'key.dev.p12',
                    'destination' => 'builder/key.dev.p12'
                )
            ),
        );
    }
}
