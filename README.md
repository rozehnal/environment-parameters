# Composer script building your detached parameters for deploying your app

This tool allows you to manage app parameters for deployment in separate repositories. The repo is fully 
compatible with all parameters from https://github.com/Incenteev/ParameterHandler.

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
             "Paro\\BuildParametersHandler\\ParametersHandler::buildParameters"
           ]
    },
    "extra": {
        "build-parameters": {
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

## Example
https://github.com/rozehnal/environment-parameters-test