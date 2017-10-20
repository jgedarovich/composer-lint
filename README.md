# jgedarovich/composer-lint

a fork of [https://github.com/Soullivaneuh/composer-lint](https://github.com/Soullivaneuh/composer-lint)


composer-lint is a plugin for Composer.

It extends the composer validate command with extra rules.

## Installation

add the following to your projects composer.json repositories section:
```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/jgedarovich/composer-lint"
    }
]
```
then run:

```bash
composer require jgedarovich/composer-lint
```

then configure it by adding a `.composerlint` file to the root of your
repository, \( [see below](#configuration) \).


## Usage

That's it! Composer will enable automatically the plugin as soon it's installed.

Just run `composer validate` command to see the plugin working.

## Configuration

You can configure the plugin via a file called `/.composerlint` in the root
 of your repository

```json
{
    "lint-rules":{
        "lint-rule-class-name-here": [ "optional-lint-rule-config-here" ],
        "lint-rule-class-name-here": [ "optional-lint-rule-config-here" ],
        "lint-rule-class-name-here": [ "optional-lint-rule-config-here" ],
    }
}
```

where 'lint-rule-class-name's  are any of the classes from the
[src/LintRules/](src/LintRules) directory, [see descriptions
belew](#lint-rules)

some lint rules take custom configuration - for instance:

```json
{
    "lint-rules":{
        "RepositoryUrlSafelistLintRule":{
            "repository-safelist": [
                "http://packagist.org/",
                "https://github.com/jgedarovich/composer-lint"
            ]
        }
    }
}
```

and

```json
{
    "lint-rules":{
        "NoFilesAutoloaderLintRule":{
            "ignore":[
                "vendor/guzzlehttp/guzzle/src/functions_include.php",
            ],
            "custom-comment":"\n\n custom error message here \n\n"
        }
    }
}

```
## Lint Rules


### ClassmapAuthoritativeLintRule 
---
Enforces that classmap-athoritative is set to true.
#### Configuration Options
None
#### Example
```json
{
    "lint-rules":{
        "ClassmapAuthoritativeLintRule": []
    }
}

```

### MinimumStabilityLintRule  
---
Checks if minimum-stability is set. It raises an error if it is, except for project packages.
#### Configuration Options
None 
#### Example
```json
{
    "lint-rules":{
        "MinimumStabilityLintRule": []
    }
}

```

### NoFilesAutoloaderLintRule
---
Enforces that no packages (including transative dependencies) are included that use the files autoloader type 
#### Configuration Options
* OPTIONAL `ignore` array of file paths that would be file autoloaded, 
* OPTIONAL `custom-comment` configurable text to append to the error message, may be useful if there is a workaround 
#### Example
```json
{
    "lint-rules":{
        "NoFilesAutoloaderLintRule":{
            "ignore":[
                "vendor/guzzlehttp/guzzle/src/functions_include.php",
            ],
            "custom-comment":"\n\n custom error message here \n\n"
        }
    }
}

```

### NoPackagistLintRule 
---
Enforces that a packagist is set to false 
#### Configuration Options
None 
##### Examples
```json
{
    "lint-rules":{
        "NoPackagistLintRule": []
    }
}

```

### NoPrependAutoLoaderLintRule 
---
Enforces that a prepend-autaloader is set is set to false 
#### Configuration Options
None 
#### Example
```json
{
    "lint-rules":{
        "NoPrependAutoLoaderLintRule": []
    }
}
```

### NoUseIncludePathLintRule 
---
Enforces that a use-include-path is set is set to false 
### Configuration Options
None 
#### Example
```json
{
    "lint-rules":{
        "NoUseIncludePathLintRule": []

    }
}
```

### PhpLintRule
---
Checks if the PHP requirement is set on the require section. 
### Configuration Options
None 
#### Example
```json
{
    "lint-rules":{
        "PhpLintRule": []
    }
}
```

### RepositoryUrlSafelistLintRule 
---
Enforces that a all configured repository urls  match one of the safelisted url's 
### Configuration Options
* REQUIRED: 'repository-safelist' array of allowed repository urls 
#### Example
```json
{
    "lint-rules":{
        "PhpLintRule": { "repository-safelist": [ "http://packagist.org" ] }
    }
}
```

### SecureHttpLintRule 
---
Enforces that secure-http  is set to true 
### Configuration Options
None 
#### Example
```json
{
    "lint-rules":{
        "SecureHttpLintRule": []
    }
}
```

### SortedPackagesLintRule 
---
Checks if packages are sorted on each section. This option is outside sllh-composer-lint because it's a composer native one. 
### Configuration Options
None 
#### Example
```json
{
    "lint-rules":{
        "SortedPackagesLintRule": []
    }
}
```

### TypeLintRule 
---
Check if package type is defined. 
### Configuration Options
None 
#### Example
```json
{
    "lint-rules":{
        "SecureHttpLintRule": []
    }
}
```

### VersionConstraintsLintRule 
---
Checks if version constraint formats are valid (e.g. ~2.0 should be ^2.0). 
### Configuration Options
None 
#### Example
```json
{
    "lint-rules":{
        "VersionConstraintsLintRule": []
    }
}
```
