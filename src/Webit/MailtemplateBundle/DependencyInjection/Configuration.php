<?php

namespace Webit\MailtemplateBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('webit_mailtemplate');

        $rootNode->children()
                   ->scalarNode('sender_email')
                      ->isRequired()
                      ->cannotBeEmpty()
                   ->end()
                   ->scalarNode('sender_name')
                      ->isRequired()
                      ->cannotBeEmpty()
                   ->end()
                   ->scalarNode('reply_email')
                      ->info('Default reply-to email')
                      ->defaultValue(null)
                   ->end()
                   ->scalarNode('default_receiver')
                      ->info('Default email receiver in case "TO" field is not set')
                      ->defaultValue(null)
                   ->end()
                   ->booleanNode('enable_log')
                      ->info('Enable logging email activity via mail template')
                      ->defaultValue(true)
                   ->end()
                   ->ArrayNode('bcc')
                      ->prototype('scalar')->end()
                      ->info('Default email to BCC in all message, like: monitoring@example.com')
                      //->defaultValue([])
                   ->end()
                   ->scalarNode('mail_layout')
                      ->info('Template for rendering email, default: WebitMailtemplateBundle:Email:template.html.twig')
                      ->defaultValue('WebitMailtemplateBundle:Email:template.html.twig')
                   ->end() 
                   ->arrayNode('queue')
                      ->addDefaultsIfNotSet()
                      ->info('information about mail queue if defined, currently not implemented')
                      ->children()
                        ->booleanNode('enabled')->defaultValue(false)->end()
                        ->scalarNode('type')->defaultValue('none')->end()
                      ->end()     
                ->end();
                 
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
