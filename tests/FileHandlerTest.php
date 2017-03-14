<?php
namespace Paro\EnvironmentParameters\Tests;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Paro\EnvironmentParameters\FileHandler;
use Symfony\Component\Filesystem\Filesystem;

class FileHandlerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var FileHandler
	 */
	private $fileHandler;

	protected function setUp() {
		parent::setUp();
		$fs = new Filesystem();
		$this->fileHandler = new FileHandler($fs, array('--env=prod'));
	}

	/**
	 * @dataProvider preparePathProvider
	 * @param $path
	 * @param $expected
	 */
	public function testPreparePath($path, $expected) {
		$this->assertEquals($expected, $this->fileHandler->preparePath($path));
	}

	public function preparePathProvider() {
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
	public function testArgumentValue($name, $expected) {
		$this->assertEquals($expected, $this->fileHandler->getArgumentValue($name));
	}

	public function argumentValueProvider() {
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
	public function testResolvePath($currentPath, $importPath, $expected) {
		$this->assertEquals($expected, $this->fileHandler->resolvePath($currentPath, $importPath));
	}

	public function resolvePathProvider() {
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

	public function testInitDirectory() {
		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));

		$buildDirName = 'createthis';
		$buildDir = vfsStream::url('root/' . $buildDirName);

		//currently doesn't exists
		$this->assertFalse(vfsStreamWrapper::getRoot()->hasChild($buildDirName));

		//create one
		$this->fileHandler->initDirectory($buildDir);
		$this->assertTrue(vfsStreamWrapper::getRoot()->hasChild($buildDirName));
		vfsStreamWrapper::unregister();
	}

	/**
	 * @param $expected
	 * @param $value
	 *
	 * @dataProvider testProcessEnvironmentalVariableProvider
	 */
	public function testProcessEnvironmentalVariable($value, $expected) {
		$this->assertEquals($expected, $this->fileHandler->processEnvironmentalVariable($value));
	}

	public function testProcessEnvironmentalVariableProvider() {
		return array(
			'string' => array(
				'value' => 'string',
				'expected' => 'string'
			),
			'number' => array(
				'value' => 444,
				'expected' => 444
			),
			'env variable' => array(
				'value' => '%env(PATH)%',
				'expected' => getenv('PATH')
			),

			'string like env' => array(
				'value' => 'env(PATH)%',
				'expected' => 'env(PATH)%'
			)
		);
	}
}
