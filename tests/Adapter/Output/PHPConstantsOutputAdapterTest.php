<?php
namespace Paro\EnvironmentParameters\Tests\Adapter\Ouput;

use Paro\EnvironmentParameters\Adapter\Output\PHPConstantsOutputAdapter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

class PHPConstantsOutputAdapterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PHPConstantsOutputAdapter
     */
    private $PHPConstantsOutputAdapter;

    protected function setUp()
    {
        parent::setUp();
        $this->PHPConstantsOutputAdapter = new PHPConstantsOutputAdapter();
    }

    public function testGetName()
    {
        $actual = $this->PHPConstantsOutputAdapter->getName();
        $this->assertEquals('php-constants', $actual);
    }

    /**
     * @param $parameters
     * @param $fileName
     * @param $env
     * @param $expected
     *
     * @dataProvider processProvider
     */
    public function testProcess($parameters, $fileName, $env, $date, $expected)
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));

        $fileNameVFS = vfsStream::url('root/' . $fileName);
        $this->PHPConstantsOutputAdapter->process($parameters, $fileNameVFS, $env, $date);
        $actual = file_get_contents(vfsStream::url('root/' . $fileName));
        $this->assertEquals($expected, $actual);
        vfsStreamWrapper::unregister();
    }

    public function processProvider()
    {
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
                'expected' => sprintf("%s\n%s\n%s\n",
                    sprintf("<?php\n/** This file is auto-generated during the build process of '%s' environment at %s **/", 'devlike/test', date(DATE_ATOM, $date)),
                    "define('PARAMETER', 1100);",
                    "define('PARAMETER1', 1.1);"
                ),

            ),
            'array' => array(
                'parameters' => array(
                    'PARAMETER' => array(100, 200, 300, "aaa'", array("'\"'")),
                    'PARAMETER1' => true,
                    'PARAMETER2' => null
                ),
                'fileName' => 'parameters.php',
                'env' => 'devlike/test',
                'date' => $date,
                'expected' => sprintf("%s\n%s\n%s\n%s\n",
                    sprintf("<?php\n/** This file is auto-generated during the build process of '%s' environment at %s **/", 'devlike/test', date(DATE_ATOM, $date)),
                    sprintf("define('PARAMETER', '%s');", "a:5:{i:0;i:100;i:1;i:200;i:2;i:300;i:3;s:4:\"aaa\\'\";i:4;a:1:{i:0;s:3:\"\\'\"\\'\";}}"),
                    "define('PARAMETER1', true);",
                    "define('PARAMETER2', null);"
                ),

            ),
        );
    }
}
