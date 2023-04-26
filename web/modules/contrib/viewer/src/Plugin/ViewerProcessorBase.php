<?php

namespace Drupal\viewer\Plugin;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\viewer\Entity\ViewerInterface;

/**
 * ViewerProcessorBase plugin base class.
 *
 * @package viewer
 */
class ViewerProcessorBase extends PluginBase implements ViewerProcessorInterface, ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getDataAsArray(ViewerInterface $viewer, $split_headers) {
    return [];
  }

}
