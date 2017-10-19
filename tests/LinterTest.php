<?php

namespace SLLH\ComposerLint\Tests;

use Composer\Json\JsonFile;
use SLLH\ComposerLint\Linter;
use SLLH\ComposerLint\LinteRule;
use SLLH\ComposerLint\ArrayOfLintRules;
use SLLH\ComposerLint\NoFilesAutoloaderLintRule;
use ArrayIterator;
use org\bovigo\vfs\vfsStream;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 * @author jimbo gedarovich <james.gedarovich@gmail.com>
 */
final class LinterTest extends \PHPUnit_Framework_TestCase
{
    public function setup() {
    }

    /**
     * @return array[]
     */
    public function getNoFilesAutoloaderLintData() {
        return array(
            array(
                array(
                    'composer.lock' => __DIR__.'/fixtures/files-autoloader-ko.lock',
                    'composer.json' => __DIR__.'/fixtures/files-autoloader-ko.json'
                ),
                [
                    "ignore" => [
                        "vendor/guzzlehttp/guzzle/src/functions_include.php",
                        "vendor/guzzlehttp/promises/src/functions_include.php",
                        "vendor/guzzlehttp/psr7/src/functions_include.php",
                    ],
                    "custom-comment" =>  "\nIn order to use ||||||top_level_package_name|||| properly, you'll need to add the necessary requires ONLY to the files that use this package so that they are only loaded on requests that use it. and in order to squelch this lint, add each of the above file names to .composerlintignore\n\n"
                ],
                0
            ),
            array(
                array(
                    'composer.lock' => __DIR__.'/fixtures/files-autoloader-ko.lock',
                    'composer.json' => __DIR__.'/fixtures/files-autoloader-ko.json'
                ),
                [],
                4
            ),
            array(
                array(
                    'composer.lock' => __DIR__.'/fixtures/files-autoloader-ok.lock',
                    'composer.json' => __DIR__.'/fixtures/files-autoloader-ok.json'
                )
            ),
            array(
                array(
                    'composer.lock' => __DIR__.'/fixtures/files-autoloader-ko2.lock',
                    'composer.json' => __DIR__.'/fixtures/files-autoloader-ko2.json'
                ),
                [],
                10
            ),
            array(
                array(
                    'composer.lock' => __DIR__.'/fixtures/files-autoloader-ko2.lock',
                    'composer.json' => __DIR__.'/fixtures/files-autoloader-ko2.json'
                ),
                [
                    "ignore" => [
                        "vendor/guzzlehttp/guzzle/src/functions_include.php",
                        "vendor/guzzlehttp/promises/src/functions_include.php",
                        "vendor/guzzlehttp/psr7/src/functions_include.php",
                        "vendor/aws/aws-sdk-php/src/functions.php",
                        "vendor/mtdowling/jmespath.php/src/JmesPath.php"

                    ],
                    "custom-comment" =>  "\nIn order to use ||||||top_level_package_name|||| properly, you'll need to add the necessary requires ONLY to the files that use this package so that they are only loaded on requests that use it. and in order to squelch this lint, add each of the above file names to .composerlintignore\n\n"
                ],
                0
            ),
        );
    }

    /**
     * @dataProvider getNoFilesAutoloaderLintData
     * @param array  $fixture_data
     * @param array  $config
     * @param int    $expectedErrorsCount
     */
    public function testNoFilesAutoloaderLint( array $fixture_data, array $config = [], int $expectedErrorsCount = 0) {
        $this->root = vfsStream::setup();


        foreach ( $fixture_data as $filename => $file_data ) {
            vfsStream::newFile($filename)
                ->withContent(file_get_contents($file_data)
            )->at($this->root);
        }

        $lint_rules = new ArrayOfLintRules(
            [
                new \SLLH\ComposerLint\NoFilesAutoloaderLintRule( $config, $this->root->url() )
            ]
        );
        $linter = new Linter( $lint_rules);
        $errors = $linter->validate([]);
        $this->assertCount($expectedErrorsCount, $errors);
    }

    /**
     * @return array[]
     */
    public function getNoFilesAutoloaderLintExceptionData()
    {
        return array(
            array(
                array()
            ),
            array(
                array('composer.json' => __DIR__.'/fixtures/files-autoloader-ok.json')
            ),
            array(
                array('composer.lock' => __DIR__.'/fixtures/files-autoloader-ok.lock')
            ),
        );
    }

    /**
     * @dataProvider getNoFilesAutoloaderLintExceptionData
     * @test
     * @expectedException  InvalidArgumentException
     * @expectedExceptionMessage  The directory "vfs://root" does not contain a composer.json and or a composer.lock - they are both required for this lint to work.
     * @param array $fixture_data
     */
    public function testNoFilesAutoloaderLintMissingFiles( array $fixture_data) {
        $this->root = vfsStream::setup();

        foreach ( $fixture_data as $filename => $file_data ) {
            vfsStream::newFile($filename)
                ->withContent(file_get_contents($file_data)
            )->at($this->root);
        }

        $lint_rules = new ArrayOfLintRules(
            [
                new \SLLH\ComposerLint\NoFilesAutoloaderLintRule( [], $this->root->url() )
            ]
        );
        $linter = new Linter( $lint_rules);
        $errors = $linter->validate([]);
    }

    /**
     * @dataProvider getLintData
     *
     * @param string $lint_class_name
     * @param string $manifest_file
     * @param int    $expectedErrorsCount
     * @param array $config
     */
    public function testLint($lint_class_name, $manifest_file, $expectedErrorsCount = 0, $config = [])
    {
        $manifest_json= new JsonFile($manifest_file);
        $manifest = $manifest_json->read();

        $lint_rules = new ArrayOfLintRules();
        $lint_rules[] = new $lint_class_name($config);
        $linter = new Linter( $lint_rules);

        $errors = $linter->validate($manifest);
        $this->assertCount($expectedErrorsCount, $errors);
    }

    /**
     * @return array[]
     */
    public function getLintData()
    {
        return array(
            array('\SLLH\ComposerLint\SortedPackagesLintRule',__DIR__.'/fixtures/sort-ko.json', 6),
            array('\SLLH\ComposerLint\SortedPackagesLintRule',__DIR__.'/fixtures/sort-ok.json'),
            array('\SLLH\ComposerLint\SortedPackagesLintRule',__DIR__.'/fixtures/sort-ok-minimal.json'),
            array('\SLLH\ComposerLint\PhpLintRule',__DIR__.'/fixtures/php-ok.json'),
            array('\SLLH\ComposerLint\PhpLintRule',__DIR__.'/fixtures/php-ko.json', 1),
            array('\SLLH\ComposerLint\PhpLintRule',__DIR__.'/fixtures/php-on-dev.json', 1),
            array('\SLLH\ComposerLint\MinimumStabilityLintRule',__DIR__.'/fixtures/minimum-stability-ok.json'),
            array('\SLLH\ComposerLint\MinimumStabilityLintRule',__DIR__.'/fixtures/minimum-stability-ko.json', 1),
            array('\SLLH\ComposerLint\MinimumStabilityLintRule',__DIR__.'/fixtures/minimum-stability-project.json'),
            array('\SLLH\ComposerLint\TypeLintRule',__DIR__.'/fixtures/type-ok.json'),
            array('\SLLH\ComposerLint\TypeLintRule',__DIR__.'/fixtures/type-ko.json', 1),
            array('\SLLH\ComposerLint\VersionConstraintsLintRule',__DIR__.'/fixtures/version-constraints-ok.json'),
            array('\SLLH\ComposerLint\VersionConstraintsLintRule',__DIR__.'/fixtures/version-constraints-ko.json', 5),
            array('\SLLH\ComposerLint\NoPrependAutoLoaderLintRule',__DIR__.'/fixtures/no-prepend-autoloader-ok.json'),
            array('\SLLH\ComposerLint\NoPrependAutoloaderLintRule',__DIR__.'/fixtures/no-prepend-autoloader-ko.json', 1),
            array('\SLLH\ComposerLint\ClassmapAuthoritativeLintRule',__DIR__.'/fixtures/classmap-authoritative-ok.json'),
            array('\SLLH\ComposerLint\ClassmapAuthoritativeLintRule',__DIR__.'/fixtures/classmap-authoritative-ko.json', 1),
            array('\SLLH\ComposerLint\NoUseIncludePathLintRule',__DIR__.'/fixtures/use-include-path-ok.json'),
            array('\SLLH\ComposerLint\NoUseIncludePathLintRule',__DIR__.'/fixtures/use-include-path-ko.json', 1),
            array('\SLLH\ComposerLint\SecureHttpLintRule',__DIR__.'/fixtures/secure-http-ok.json'),
            array('\SLLH\ComposerLint\SecureHttpLintRule',__DIR__.'/fixtures/secure-http-ko.json', 1),
            array('\SLLH\ComposerLint\NoPackagistLintRule',__DIR__.'/fixtures/no-packagist-ok.json'),
            array('\SLLH\ComposerLint\NoPackagistLintRule',__DIR__.'/fixtures/no-packagist-ko.json', 1),
            array('\SLLH\ComposerLint\RepositoryUrlSafelistLintRule',__DIR__.'/fixtures/repository-safelist-ok.json', 0, ['repository-safelist'=>["http://packagist.org/"]]),
            array('\SLLH\ComposerLint\RepositoryUrlSafelistLintRule',__DIR__.'/fixtures/repository-safelist-ko.json', 1),
            array('\SLLH\ComposerLint\RepositoryUrlSafelistLintRule',__DIR__.'/fixtures/repository-safelist-ko.json', 1, ['repository-safelist'=>["http://notpackagist.org/"]]),
        );
    }
}
