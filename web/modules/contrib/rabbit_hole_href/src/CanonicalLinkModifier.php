<?php

namespace Drupal\rabbit_hole_href;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\rabbit_hole\Plugin\RabbitHoleBehaviorPluginManager;

/**
 * Modifies the canonical link.
 *
 * @package Drupal\rabbit_hole_href
 */
class CanonicalLinkModifier {

  /**
   * Drupal\rabbit_hole\Plugin\RabbitHoleBehaviorPluginManager definition.
   *
   * @var Drupal\rabbit_hole\Plugin\RabbitHoleBehaviorPluginManager
   */
  protected $rhBehaviorPluginManager;

  /**
   * Constructor.
   */
  public function __construct(RabbitHoleBehaviorPluginManager $plugin_manager_rabbit_hole_behavior_plugin) {
    $this->rhBehaviorPluginManager = $plugin_manager_rabbit_hole_behavior_plugin;
  }

  /**
   * Get the original canonical url.
   */
  public function getCanonicalUrl(ContentEntityInterface $entity) {
    return $this->getNewCanonicalUrl($entity);
  }

  /**
   * Get the new url to redirect to.
   */
  protected function getNewCanonicalUrl(ContentEntityInterface $entity) {
    /*
    array(13) {
    [0]=>
    string(11) "__construct"
    [1]=>
    string(15) "setCacheBackend"
    [2]=>
    string(14) "getDefinitions"
    [3]=>
    string(22) "clearCachedDefinitions"
    [4]=>
    string(9) "useCaches"
    [5]=>
    string(17) "processDefinition"
    [6]=>
    string(16) "getCacheContexts"
    [7]=>
    string(12) "getCacheTags"
    [8]=>
    string(14) "getCacheMaxAge"
    [9]=>
    string(13) "getDefinition"
    [10]=>
    string(14) "createInstance"
    [11]=>
    string(11) "getInstance"
    [12]=>
    string(13) "hasDefinition"
    }
     */
    return $this->rhBehaviorPluginManager->createInstance('page_redirect_href')->getActionTarget($entity);
  }

}
