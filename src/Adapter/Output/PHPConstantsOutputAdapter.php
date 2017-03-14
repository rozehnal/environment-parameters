<?php

namespace Paro\EnvironmentParameters\Adapter\Output;


class PHPConstantsOutputAdapter implements OutputAdapterInterface
{
    public static function getName()
    {
        return 'php-constants';
    }

    public function process($parameters, $fileName, $env)
    {
        $content = sprintf("<?php\n/** This file is auto-generated during the build process of '%s' environment at %s **/\n", $env, date(DATE_ATOM));
        foreach ($parameters as $key => $value) {
            $content .= sprintf("define('%s', %s);\n", $key, $this->serialize($value));
        };
        file_put_contents($fileName, $content);
    }

    /**
     * @param $value
     * @return string
     */
    protected function serialize($value)
    {
        switch (gettype($value)) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'array':
            case 'object':
                return "'" . $this->addSlashes(serialize($value)) . "'";
            case 'NULL':
                return "null";
            case 'integer':
            case 'double':
                return $value;
            default:
                return "'" . $this->addSlashes($value) . "'";
        }
    }

    private function addSlashes($value)
    {
        return str_replace("'", "\\'", $value);
    }

}