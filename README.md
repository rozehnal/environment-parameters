# Composer script building your detached parameters for deploying the app

This tool allows you to manage app parameters for deployment in separate repositories. The repo is fully 
compatible with all parameters from https://github.com/rozehnal/ParameterHandler .

## Run
``composer run-script build --no-interaction``

## Usage

Add the following in your root composer.json file:

```json
{
    "require": {
        "rozehnal//environment-parameters": "*"
    },
    "scripts": {
       "build": [
             "Paro\\BuildParametersHandler\\ParametersHandler::buildParameters"
           ]
    },
    "extra": {
        "build-parameters": {
          "build-folder": "build",
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