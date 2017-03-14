<?php
namespace Paro\EnvironmentParameters\Tests\Adapter\Ouput;

use Paro\EnvironmentParameters\Adapter\Output\PHPConstantsOutputAdapter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

class PHPConstantsOutputAdapterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var PHPConstantsOutputAdapter
	 */
	private $PHPConstantsOutputAdapter;

	protected function setUp() {
		parent::setUp();
		$this->PHPConstantsOutputAdapter = new PHPConstantsOutputAdapter();
	}

	/**
	 * @param $parameters
	 * @param $fileName
	 * @param $env
	 * @param $expected
	 *
	 * @dataProvider processProvider
	 */
	public function testProcess($parameters, $fileName, $env, $expected) {
		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));

		$fileNameVFS = vfsStream::url('root/' . $fileName);
		$this->PHPConstantsOutputAdapter->process($parameters, $fileNameVFS, $env);
		$actual = file_get_contents(vfsStream::url('root/' . $fileName));
		$this->assertEquals($expected, $actual);
		vfsStreamWrapper::unregister();
	}

	public function processProvider() {
		return array(
			'number' => array(
				'parameters' => array(
					'PARAMETER' => 1100,
					'PARAMETER1' => 1.100
				),
				'fileName' => 'parameters.php',
				'env' => 'devlike/test',
				'expected' => sprintf("%s\n%s\n%s\n",
					sprintf("<?php\n/** This file is auto-generated during the build process of '%s' environment at %s **/", 'devlike/test', date(DATE_ATOM)),
					"define('PARAMETER', 1100);",
					"define('PARAMETER1', 1.1);"
				),

			),
			'array' => array(
				'parameters' => array(
					'PARAMETER' => array(100, 200, 300, "aaa'", array("'\"'"))
				),
				'fileName' => 'parameters.php',
				'env' => 'devlike/test',
				'expected' => sprintf("%s\n%s\n",
					sprintf("<?php\n/** This file is auto-generated during the build process of '%s' environment at %s **/", 'devlike/test', date(DATE_ATOM)),
					sprintf("define('PARAMETER', '%s');", "a:5:{i:0;i:100;i:1;i:200;i:2;i:300;i:3;s:4:\"aaa\\'\";i:4;a:1:{i:0;s:3:\"\\'\"\\'\";}}")
				),

			),
		);
	}
}
