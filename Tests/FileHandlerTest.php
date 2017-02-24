<?php
namespace Incenteev\ParameterHandler\Tests;

use Paro\BuildParametersHandler\FileHandler;

class FileHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var FileHandler
     */
    private $fileHandler;

    protected function setUp()
    {
        parent::setUp();
        $this->fileHandler = new FileHandler();
    }

    /**
     * @dataProvider testPreparePathProvider
     * @param $path
     * @param $arguments
     * @param $expected
     */
    public function testPreparePath($path, $arguments, $expected)
    {
        $this->assertEquals($expected, $this->fileHandler->preparePath($path, $arguments));
    }

    public function testPreparePathProvider()
    {
        return array(
            'without env parameter' => array(
                'path' => 'folder/parameters.yml',
                'arguments' => array('--env=prod'),
                'expected' => 'folder/parameters.yml'
            ),
            'with env parameter' => array(
                'path' => '{env}/parameters.yml',
                'arguments' => array('--env=prod'),
                'expected' => 'prod/parameters.yml'
            )
        );
    }

}