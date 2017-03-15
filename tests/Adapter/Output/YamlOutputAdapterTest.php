<?php
namespace Paro\EnvironmentParameters\Tests\Adapter\Ouput;

use Paro\EnvironmentParameters\Adapter\Output\YamlOutputAdapter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

class YamlOutputAdapterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var YamlOutputAdapter
	 */
	private $YamlOutputAdapter;

	protected function setUp() {
		parent::setUp();
		$this->YamlOutputAdapter = new YamlOutputAdapter('parameters');
	}

	/**
	 * @param $parameters
	 * @param $fileName
	 * @param $env
	 * @param $expected
	 *
	 * @dataProvider processProvider
	 */
	public function testProcess($parameters, $fileName, $env, $date, $expected) {
		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));

		$fileNameVFS = vfsStream::url('root/' . $fileName);
		$this->YamlOutputAdapter->process($parameters, $fileNameVFS, $env, $date);
		$actual = file_get_contents(vfsStream::url('root/' . $fileName));
		$this->assertEquals($expected, $actual);
		vfsStreamWrapper::unregister();
	}

	public function processProvider() {
		$date = time();
		return array(
			'number' => array(
				'parameters' => array(
					'PARAMETER' => 1100,
					'PARAMETER1' => 1.100
				),
				'fileName' => 'parameters.php',
				'env' => 'devlike/test',
				'date' => $date,
				'expected' => sprintf("%s\n%s\n%s\n%s\n",
					sprintf("# This file is auto-generated during the build process of '%s' environment at %s", 'devlike/test', date(DATE_ATOM, $date)),
					"parameters:",
					"    PARAMETER: 1100",
					"    PARAMETER1: 1.1"
				),

			),
			'array' => array(
				'parameters' => array(
					'PARAMETER' => array(100, 200, 300, "aaa'", array("'\"'"))
				),
				'fileName' => 'parameters.php',
				'env' => 'devlike/test',
				'date' => $date,
				'expected' => sprintf("%s\n%s\n%s\n",
					sprintf("# This file is auto-generated during the build process of '%s' environment at %s", 'devlike/test', date(DATE_ATOM, $date)),
                    "parameters:",
                    "    PARAMETER: [100, 200, 300, 'aaa''', ['''\"''']]"
				),

			),
		);
	}
}
