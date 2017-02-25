<?php
namespace Paro\BuildParametersHandler\Tests;

use Paro\BuildParametersHandler\FileProcessor;

class FileProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider processProvider
     * @param $configs
     * @param $expected
     */
    public function testProcess($configs, $expected)
    {
        $fs = $this->getMockBuilder('Symfony\\Component\\Filesystem\\Filesystem')
            ->setMethods(array('copy'))
            ->getMock();

        $fs->expects($this->once())
            ->method('copy')
            ->with($this->equalTo($expected['source']), $this->equalTo($expected['destination']))
            ->willReturn(true);

        $fileHandler = $this->getMockBuilder('Paro\\BuildParametersHandler\\FileHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getArgumentValue'))
            ->getMock();

        $fileHandler->expects($this->once())
            ->method('getArgumentValue')
            ->with($this->equalTo('env'))
            ->willReturn('prod');

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
            'empty config simple' => array(
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
            'empty config' => array(
                'configs' => array(
                    'build-folder' => 'builder',
                    'files' => array(
                        array(
                            'file' => 'key.{env}.p12',
                        )
                    ),
                ),
                'expected' => array(
                    'source' => 'key.prod.p12',
                    'destination' => 'builder/key.prod.p12'
                )
            ),
        );
    }
}
