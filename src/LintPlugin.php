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
     * todo make this not suck
     */
    public function __construct( $config = [] ){
        // /vendor/jgedarovich/composer-lint/src/
        if( empty($config) &&  file_exists('../../../../.composerlint') ) {
            //todo try/catch error - or splt this confi gparsing login into another class
            $this->config = json_decode(file_get_contents('../../../../.composerlint'), true);
        } else {
            $this->config = $config;
        }
        $this->lint_rules = new ArrayOfLintRules();
        $all_lint_rules = array_map(function($filename) {
            return basename($filename, '.php');
        },glob(dirname(__FILE__).'/LintRules/*.php'));

        if ( isset($config['lint_rules']))  {
            foreach ( $config['lint_rules'] as $lint_class_name => $lint_class_config ) {
                //check if it's one of the available classes
                if ( in_array($lint_class_name, $all_lint_rules) ) {
                    $namespaced_lint_class_name=__NAMESPACE__."\\".$lint_class_name;
                    //todo - make sure config is an array or some other type?
                    $this->lint_rules[] = new $namespaced_lint_class_name($lint_class_config);
                }
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
         //eval(\Psy\sh());
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
