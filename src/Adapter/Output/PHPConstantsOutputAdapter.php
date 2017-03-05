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
            $content .= sprintf("define('%s', '%s');\n", $key, is_array($value) ? serialize($value) : addslashes($value));
        };
        file_put_contents($fileName, $content, 99);
    }

}