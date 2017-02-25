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
     * @dataProvider preparePathProvider
     * @param $path
     * @param $expected
     */
    public function testPreparePath($path, $expected)
    {
        $this->assertEquals($expected, $this->fileHandler->preparePath($path));
    }

    public function preparePathProvider()
    {
        return array(
            'without env parameter' => array(
                'path' => 'folder/parameters.yml',
                'expected' => 'folder/parameters.yml'
            ),
            'with env parameter' => array(
                'path' => '{env}/parameters.yml',
                'expected' => 'prod/parameters.yml'
            ),
            'with different parameter' => array(
                'path' => '{enev}/parameters.yml',
                'expected' => '{enev}/parameters.yml'
            )
        );
    }

    /**
     * @param $name
     * @param $expected
     *
     * @dataProvider argumentValueProvider
     */
    public function testArgumentValue($name, $expected)
    {
        $this->assertEquals($expected, $this->fileHandler->getArgumentValue($name));
    }

    public function argumentValueProvider()
    {
        return array(
            'env found' => array(
                'name' => 'env',
                'expected' => 'prod',
            ),
            '--env not found' => array(
                'name' => '--env',
                'expected' => false,
            )
        );
    }


    /**
     * @param $currentPath
     * @param $importPath
     * @param $expected
     * @dataProvider resolvePathProvider
     */
    public function testResolvePath($currentPath, $importPath, $expected)
    {
        $this->assertEquals($expected, $this->fileHandler->resolvePath($currentPath, $importPath));
    }

    public function resolvePathProvider()
    {
        return array(
            'parent path' => array(
                'currentPath' => '/home/user/dir/current.yml',
                'importPath' => '../import.yml',
                'expected' => '/home/user/dir/../import.yml',
            ),
            'current path' => array(
                'currentPath' => '/home/user/dir/current.yml',
                'importPath' => './import.yml',
                'expected' => '/home/user/dir/./import.yml',
            ),

            'current simple path' => array(
                'currentPath' => '/home/user/dir/current.yml',
                'importPath' => 'import.yml',
                'expected' => '/home/user/dir/import.yml',
            ),
            'absolute path' => array(
                'currentPath' => '/home/user/dir/current.yml',
                'importPath' => '/import.yml',
                'expected' => '/import.yml',
            )
        );
    }

    public function testInitDirectory()
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testInitDirectory';

        if (is_dir($dir)) {
            rmdir($dir);
        }

        $this->assertFalse(is_dir($dir));

        //create one
        $this->fileHandler->initDirectory($dir);
        $this->assertTrue(is_dir($dir));
        //if is existing
        $this->fileHandler->initDirectory($dir);
        $this->assertTrue(is_dir($dir));

    }

}
