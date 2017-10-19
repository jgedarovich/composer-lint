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
repository, \( [see below](#Configuration) \).


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
[src/LintRules/](src/LintRules) directory
