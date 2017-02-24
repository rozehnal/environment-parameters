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
        $this->fileHandler = new FileHandler(array('--env=prod'));
    }

    /**
     * @dataProvider testPreparePathProvider
     * @param $path
     * @param $expected
     */
    public function testPreparePath($path, $expected)
    {
        $this->assertEquals($expected, $this->fileHandler->preparePath($path));
    }

    public function testPreparePathProvider()
    {
        return array(
            'without env parameter' => array(
                'path' => 'folder/parameters.yml',
                'expected' => 'folder/parameters.yml'
            ),
            'with env parameter' => array(
                'path' => '{env}/parameters.yml',
                'expected' => 'prod/parameters.yml'
            )
        );
    }

}
