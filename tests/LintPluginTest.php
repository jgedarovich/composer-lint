<?php

namespace SLLH\ComposerLint\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\BufferIO;
use Composer\Package\RootPackage;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PluginManager;
use SLLH\ComposerLint\LintPlugin;
use Symfony\Component\Console\Output\NullOutput;
use org\bovigo\vfs\vfsStream;
use SLLH\ComposerLint\ArrayOfLintRules;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class LintPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BufferIO
     */
    private $io;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Composer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->io = new BufferIO();
        $this->composer = new Composer();
        $this->config = new Config(false);

        $this->composer->setPluginManager(new PluginManager($this->io, $this->composer));
        $this->composer->setEventDispatcher(new EventDispatcher($this->composer, $this->io));
        $this->composer->setConfig($this->config);
        $this->composer->setPackage(new RootPackage('root/root', '1.0.0', '1.0.0'));
    }

    public function testLintPluginConfigFromArrayNotFile() {

        $config = [
            "lint-rules" => [
                '\SLLH\ComposerLint\PhpLintRule' => [],
                '\SLLH\ComposerLint\TypeLintRule' => [],
            ]
        ];


        $lint_rules = $this->createMock('\SLLH\ComposerLint\ArrayOfLintRules');
        $lint_rules->expects($this->exactly(2))
            ->method('addLint')
            ->withConsecutive(
                [$this->equalTo('\SLLH\ComposerLint\PhpLintRule'), $this->equalTo([])],
                [$this->equalTo('\SLLH\ComposerLint\TypeLintRule'), $this->equalTo([])]
            );
        $this->root = vfsStream::setup();
        vfsStream::newFile(".composerlint")
            ->withContent(json_encode(
                    [
                        "lint-rules"=> [
                            "\SLLH\ComposerLint\SecureHttpLintRule"=> []
                        ]
                    ]
                )
            )->at($this->root);
        $plugin = new LintPlugin($config, $this->root->url(), $lint_rules);
    }

    public function testLintPluginConfigFromfile() {

        $lint_rules = $this->createMock('\SLLH\ComposerLint\ArrayOfLintRules');
        $lint_rules->expects($this->exactly(2))
            ->method('addLint')
            ->withConsecutive(
                [$this->equalTo('\SLLH\ComposerLint\TypeLintRule'), $this->equalTo([])],
                [$this->equalTo('\SLLH\ComposerLint\SecureHttpLintRule'), $this->equalTo([])]
            );
        $this->root = vfsStream::setup();
        vfsStream::newFile(".composerlint")
            ->withContent(json_encode(
                    [
                        "lint-rules"=> [
                            "\SLLH\ComposerLint\TypeLintRule"=> [],
                            "\SLLH\ComposerLint\SecureHttpLintRule"=> []
                        ]
                    ]
                )
            )->at($this->root);
        $plugin = new LintPlugin([], $this->root->url(), $lint_rules);
    }
    /**
     * @test
     * @expectedException  InvalidArgumentException
     * @expectedExceptionMessage  The composer-linter requires some lint rules to be configured, typically by adding config to a file called .composerlintignore in the root of the projects repository
     */
    public function testLintPluginNoConfig() {
        $plugin = new LintPlugin([]);
    }

    /**
     * @test
     * @expectedException  InvalidArgumentException
     * @expectedExceptionMessage  the .composerlint configuration file needs to be a well formed json file, received exception trying to decode .composerlint file: Syntax error
     */
    public function testLintPluginConfigFileIsBad() {

        $this->root = vfsStream::setup();
        vfsStream::newFile(".composerlint")
            ->withContent("not json")->at($this->root);
        $plugin = new LintPlugin([], $this->root->url());
    }

    public function testValidateCommand()
    {
        $config = [
            "lint-rules" => [
                'SortedPackagesLintRule' => [],
                'PhpLintRule' => [],
                'TypeLintRule' => [],
                'VersionConstraintsLintRule' => [],
            ]
        ];

        $this->addComposerPlugin(new LintPlugin($config));

        $input = $this->createMock('Symfony\Component\Console\Input\InputInterface');
        $input->expects($this->once())->method('getArgument')->with('file')
            ->willReturn(__DIR__.'/fixtures/composer.json');

        $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'validate', $input, new NullOutput());

        $this->assertSame(1, $this->composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent));
        $this->assertSame(<<<'EOF'
Links under require section are not sorted.
Links under require-dev section are not sorted.
You must specify the PHP requirement.
The package type is not specified.
Requirement format of 'sllh/php-cs-fixer-styleci-bridge:~2.0' is not valid. Should be '^2.0'.

EOF
            , $this->io->getOutput());
    }

    public function testValidateWithConfigCommand()
    {
        $config = [
            "lint-rules" => [
                'PhpLintRule' => [],
                'TypeLintRule' => [],
                'VersionConstraintsLintRule' => [],
            ]
        ];
        $this->addComposerPlugin(new LintPlugin($config));

        $input = $this->createMock('Symfony\Component\Console\Input\InputInterface');
        $input->expects($this->once())->method('getArgument')->with('file')
            ->willReturn(__DIR__.'/fixtures/composer.json');

        $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'validate', $input, new NullOutput());

        $this->assertSame(1, $this->composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent));
        $this->assertSame(<<<'EOF'
You must specify the PHP requirement.
The package type is not specified.
Requirement format of 'sllh/php-cs-fixer-styleci-bridge:~2.0' is not valid. Should be '^2.0'.

EOF
            , $this->io->getOutput());
    }

    /**
     * The plugin should not be executed at all.
     */
    public function testDummyCommand()
    {
        $config = [
            "lint-rules" => [
                'SortedPackagesLintRule' => [],
            ]
        ];
        $this->addComposerPlugin(new LintPlugin($config));

        $input = $this->createMock('Symfony\Component\Console\Input\InputInterface');
        $input->expects($this->never())->method('getArgument')->with('file');

        $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'dummy', $input, new NullOutput());

        $this->assertSame(0, $this->composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent));
        $this->assertSame(<<<'EOF'

EOF
            , $this->io->getOutput());
    }

    private function addComposerPlugin(PluginInterface $plugin)
    {
        $pluginManagerReflection = new \ReflectionClass($this->composer->getPluginManager());
        $addPluginReflection = $pluginManagerReflection->getMethod('addPlugin');
        $addPluginReflection->setAccessible(true);
        $addPluginReflection->invoke($this->composer->getPluginManager(), $plugin);
    }
}
