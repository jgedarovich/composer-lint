<?php

namespace SLLH\ComposerLint\Tests;

use Composer\Json\JsonFile;
use SLLH\ComposerLint\Linter;
use SLLH\ComposerLint\LinteRule;
use SLLH\ComposerLint\ArrayOfLintRules;
use ArrayIterator;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class LinterTest extends \PHPUnit_Framework_TestCase
{
    public function setup() {
    }

    /**
     * @dataProvider getLintData
     *
     * @param string $lint_class_name
     * @param string $manifest_file
     * @param int    $expectedErrorsCount
     */
    public function testLint($lint_class_name, $manifest_file, $expectedErrorsCount = 0)
    {
        $manifest_json= new JsonFile($manifest_file);
        $manifest = $manifest_json->read();

        $lint_rules = new ArrayOfLintRules();
        $lint_rules[] = new $lint_class_name([]);
        $linter = new Linter( $lint_rules);

        $errors = $linter->validate($manifest);
        /**
        var_dump($manifest);
        var_dump($errors);
        echo "\n\n\n";
        **/
        $this->assertCount($expectedErrorsCount, $errors);
    }

    /**
     * @return array[]
     */
    public function getLintData()
    {
        return array(
            array('SLLH\ComposerLint\SortedPackagesLintRule',__DIR__.'/fixtures/sort-ok.json'),
            array('SLLH\ComposerLint\SortedPackagesLintRule',__DIR__.'/fixtures/sort-ok-minimal.json'),
            array('SLLH\ComposerLint\SortedPackagesLintRule',__DIR__.'/fixtures/sort-ko.json', 6),
            //array('SLLH\ComposerLint\SortedPackagesLintRule',__DIR__.'/fixtures/sort-ko-disabled.json'),
            //array('SLLH\ComposerLint\SortedPackagesLintRule',__DIR__.'/fixtures/sort-ko-no-config.json'),
            array('SLLH\ComposerLint\PhpLintRule',__DIR__.'/fixtures/php-ok.json'),
            array('SLLH\ComposerLint\PhpLintRule',__DIR__.'/fixtures/php-ko.json', 1),
            array('SLLH\ComposerLint\PhpLintRule',__DIR__.'/fixtures/php-on-dev.json', 1),
            //array('SLLH\ComposerLint\PhpLintRule',__DIR__.'/fixtures/php-ko-disabled.json'),
            array('SLLH\ComposerLint\MinimumStabilityLintRule',__DIR__.'/fixtures/minimum-stability-ok.json'),
            array('SLLH\ComposerLint\MinimumStabilityLintRule',__DIR__.'/fixtures/minimum-stability-ko.json', 1),
            array('SLLH\ComposerLint\MinimumStabilityLintRule',__DIR__.'/fixtures/minimum-stability-project.json'),
            //array(__DIR__.'/fixtures/minimum-stability-ko-disabled.json'),
            array('SLLH\ComposerLint\TypeLintRule',__DIR__.'/fixtures/type-ok.json'),
            array('SLLH\ComposerLint\TypeLintRule',__DIR__.'/fixtures/type-ko.json', 1),
            //array('SLLH\ComposerLint\MinimumStabilityLintRule',__DIR__.'/fixtures/type-ko-disabled.json'),
            array('SLLH\ComposerLint\VersionConstraintsLintRule',__DIR__.'/fixtures/version-constraints-ok.json'),
            array('SLLH\ComposerLint\VersionConstraintsLintRule',__DIR__.'/fixtures/version-constraints-ko.json', 5),
            //array('SLLH\ComposerLint\VersionConstraintsLintRule',__DIR__.'/fixtures/version-constraints-ko-disabled.json'),
            array('SLLH\ComposerLint\NoPrependAutoLoaderLintRule',__DIR__.'/fixtures/no-prepend-autoloader-ok.json'),
            //array('SLLH\ComposerLint\NoPrependAutoloaderLintRule',__DIR__.'/fixtures/no-prepend-autoloader-ko-disabled.json'),
            array('SLLH\ComposerLint\NoPrependAutoloaderLintRule',__DIR__.'/fixtures/no-prepend-autoloader-ko.json', 1),
            array('SLLH\ComposerLint\ClassmapAuthoritativeLintRule',__DIR__.'/fixtures/classmap-authoritative-ok.json'),
            array('SLLH\ComposerLint\ClassmapAuthoritativeLintRule',__DIR__.'/fixtures/classmap-authoritative-ko.json', 1),
            //array('SLLH\ComposerLint\ClassmapAuthoritativeLintRule',__DIR__.'/fixtures/classmap-authoritative-ko-disabled.json'),
            array('SLLH\ComposerLint\NoUseIncludePathLintRule',__DIR__.'/fixtures/use-include-path-ok.json'),
            array('SLLH\ComposerLint\NoUseIncludePathLintRule',__DIR__.'/fixtures/use-include-path-ko.json', 1),
            //array('SLLH\ComposerLint\UseIncludePathLintRule',__DIR__.'/fixtures/use-include-path-ko-disabled.json'),
            array('SLLH\ComposerLint\SecureHttpLintRule',__DIR__.'/fixtures/secure-http-ok.json'),
            array('SLLH\ComposerLint\SecureHttpLintRule',__DIR__.'/fixtures/secure-http-ko.json', 1),
            //array('SLLH\ComposerLint\SecureHttpLintRule',__DIR__.'/fixtures/secure-http-ko-disabled.json'),
            array('SLLH\ComposerLint\NoPackagistLintRule',__DIR__.'/fixtures/no-packagist-ok.json'),
            array('SLLH\ComposerLint\NoPackagistLintRule',__DIR__.'/fixtures/no-packagist-ko.json', 1),
            //array('SLLH\ComposerLint\NoPackagistLintRule',__DIR__.'/fixtures/-ko-disabled.json'),
            /*
            array('SLLH\ComposerLint\LintRule',__DIR__.'/fixtures/-ok.json'),
            array('SLLH\ComposerLint\LintRule',__DIR__.'/fixtures/-ko.json', 1),
            array('SLLH\ComposerLint\LintRule',__DIR__.'/fixtures/-ko-disabled.json'),
             */

        );
    }
}
