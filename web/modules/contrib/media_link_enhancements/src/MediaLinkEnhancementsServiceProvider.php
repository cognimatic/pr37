<?php

namespace Drupal\media_link_enhancements;

use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The media link enhancements service provider.
 */
class MediaLinkEnhancementsServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {

    // Override the core link generator service.
    $definition = $container->getDefinition('link_generator');
    $definition->setClass('Drupal\media_link_enhancements\LinkGenerator\MediaLinkEnhancementsLinkGenerator');
    $definition->setArguments(
      [
        new Reference('url_generator'),
        new Reference('module_handler'),
        new Reference('renderer'),
        new Reference('entity_type.manager'),
        new Reference('config.factory'),
        new Reference('media_link_enhancements.append_text'),
        new Reference('media_link_enhancements.helper'),
      ]
    );

    // Override the core url generator service.
    $definition = $container->getDefinition('url_generator.non_bubbling');
    $definition->setClass('Drupal\media_link_enhancements\Routing\MediaLinkEnhancementsUrlGenerator');
    $protocols = $container->getParameter('filter_protocols');
    $definition->setArguments(
      [
        new Reference('router.route_provider'),
        new Reference('path_processor_manager'),
        new Reference('route_processor_manager'),
        new Reference('request_stack'),
        $protocols,
        new Reference('entity_type.manager'),
        new Reference('config.factory'),
        new Reference('media_link_enhancements.helper'),
      ]
    );

  }

}
