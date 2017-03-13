# Composer script building your detached parameters for deploying your app

This tool allows you to manage app parameters for deployment in separate repositories. The repo is fully 
compatible with all parameters from https://github.com/Incenteev/ParameterHandler.


[![Build Status](https://travis-ci.org/rozehnal/environment-parameters.png)](https://travis-ci.org/rozehnal/environment-parameters)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rozehnal/environment-parameters/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/rozehnal/environment-parameters/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/rozehnal/environment-parameters/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/rozehnal/environment-parameters/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6e3890ac-6436-4166-afd4-f87d089e1774/mini.png)](https://insight.sensiolabs.com/projects/6e3890ac-6436-4166-afd4-f87d089e1774)
[![Latest Unstable Version](https://poser.pugx.org/rozehnal/environment-parameters/v/unstable.png)](https://packagist.org/packages/rozehnal/environment-parameters/)
[![Latest Stable Version](https://poser.pugx.org/rozehnal/environment-parameters/v/stable)](https://packagist.org/packages/rozehnal/environment-parameters)

## Run
``composer run-script build --no-interaction -- --env=prod``

## Usage
Add the following in your root composer.json file:

```json
{
    "require": {
        "rozehnal/environment-parameters": "0.x-dev"
    },
    "scripts": {
       "build": [
             "Paro\\EnvironmentParameters\\ParametersHandler::buildParameters"
           ]
    },
    "extra": {
        "environment-parameters": {
          "build-folder": "build",
          "files": [
			{
			  "file": "{env}/key.{env}.p12",
			  "name": "key.p12"
			}
          ],
          "incenteev-parameters": {
            "file": "parameters.yml",
            "env-map": {
              "path": "PATH"
            }
          }
        }
      }
}
```

The ``build/parameters.yml`` will then be created
composer script, to match the structure of the dist file ``parameters.yml``
by asking you the missing parameters.

## Supported syntax
 - Fully compatible with https://github.com/Incenteev/ParameterHandler
 - ``"%env(ENV_VARIABLE)%"`` syntax in ``*.yml`` files
 - ``imports`` in ``*.yml`` files
```
 imports:
     - { resource: 'include.yml' }
```

## Hierarchical structure

```json
{
    "extra": {
        "environment-parameters": {
          "files": [
			{
			  "file": "{env}/key.p12",
			  "name": "key.p12"
			}
          ],
          "incenteev-parameters": {
            "file": "{env}/parameters.yml"
          }
        }
    }
}
```

``composer run-script build --no-interaction -- --env=test/test01``

Files are searched in order ``test/test01/key.p12``, ``test/key.p12`` 
and ``test/test01/parameters.yml``, ``test/parameters.yml``. It means you are able to build configuration
on inheritence from parent folders with overriding details in children folders. Applicable for both -
files and ``*.yml`` files.

## Output formats [yaml, php-constants]
Default output format is well-known``yaml`` file. Currently there is possible to create ``php`` 
file where each parameter defines constant - ``define(key, value)``.

```json
{
    "extra": {
        "environment-parameters": {
          "incenteev-parameters": {
            "file": "{env}/parameters.yml",
            "name": "parameters.php",
            "output-format": "php-constants"
          }
        }
    }
}
```

## Example
https://github.com/rozehnal/environment-parameters-test

## Todo

