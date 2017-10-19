<?php

namespace SLLH\ComposerLint;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class LintPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Linter
     */
    private $linter;

    /**
     * @var Array
     */
    private $config;

    /**
     * @var ArrayOfLintRules
     */
    private $lint_rules;

    /*
     * it felt wierd to have the config for the linter exist
     * in the thing being linted - so I broke this out into separate file
     *
     * todo make this suck less
     * @param array $config
     * @param dir $dir
     * @param ArrayOfLintRules $lint_rules
     */
    public function __construct( $config = [], $dir = "", $lint_rules = [])
    {

        if( empty($config) &&  file_exists($dir.'/.composerlint') ) {
            $this->config = json_decode(file_get_contents($dir.'/.composerlint'), true);
            if ( is_null($this->config) ) {
                throw new \InvalidArgumentException('the .composerlint configuration file needs to be a well formed json file, received exception trying to decode .composerlint file: '.json_last_error_msg());
            }
        } elseif (empty($config) ) {
            //TODO: add link to readme or docs describing the config file syntax
            throw new \InvalidArgumentException('The composer-linter requires some lint rules to be configured, typically by adding config to a file called .composerlintignore in the root of the projects repository');
        } else {
            $this->config = $config;
        }

        //make it testable
        $this->lint_rules  = $lint_rules instanceof \SLLH\ComposerLint\ArrayOfLintRules ? $lint_rules : new \SLLH\ComposerLint\ArrayOfLintRules();

        if ( isset($this->config['lint-rules']))  {
            foreach ( $this->config['lint-rules'] as $lint_class_name => $lint_class_config ) {
                $this->lint_rules->addLint($lint_class_name, $lint_class_config);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->linter = new Linter($this->lint_rules);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            PluginEvents::COMMAND => array(
                array('command'),
            ),
        );
    }

    /**
     * @param CommandEvent $event
     *
     * @return bool true if no violation, false otherwise.
     */
    public function command(CommandEvent $event)
    {
        if ('validate' !== $event->getCommandName()) {
            return true;
        }

        $json = new JsonFile($event->getInput()->getArgument('file'));
        $manifest = $json->read();

        $errors = $this->linter->validate($manifest);

        foreach ($errors as $error) {
            $this->io->writeError(sprintf('<error>%s</error>', $error));
        }

        return empty($errors);
    }
}
