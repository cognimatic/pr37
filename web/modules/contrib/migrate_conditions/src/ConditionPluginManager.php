<?php

namespace Drupal\migrate_conditions;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages migrate condition plugins.
 *
 * @ingroup migration
 */
class ConditionPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/migrate_conditions/condition', $namespaces, $module_handler, 'Drupal\migrate_conditions\ConditionInterface', 'Drupal\migrate_conditions\Annotation\MigrateConditionsConditionPlugin');
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    // Prefixing a plugin id with 'not:' is an alternative to explicitly
    // setting the 'negate' configuration.
    if (str_starts_with($plugin_id, 'not:')) {
      $plugin_id = substr($plugin_id, 4);
      if (isset($configuration['negate']) && $configuration['negate'] === TRUE) {
        $configuration['negate'] = FALSE;
      }
      else {
        $configuration['negate'] = TRUE;
      }
      // We call 'this' method again so that multiple 'not:' prefixes are
      // handled as one might expect, even if there's no good reason to
      // do that. We might as well handle it!
      return $this->createInstance($plugin_id, $configuration);
    }
    // Some plugins can accept a single config value in parens.
    if (strpos($plugin_id, '(') > -1) {
      $matches = [];
      $pattern = '/\([\d\D]+\)$/';
      if (preg_match($pattern, $plugin_id, $matches)) {
        $plugin_id = str_replace($matches[0], '', $plugin_id);
        $definitions = $this->getDefinitions();
        if ($definition = $definitions[$plugin_id] ?? NULL) {
          $parens = $definition['parens'] ?? NULL;
          if ($parens) {
            $configuration[$parens] = substr($matches[0], 1, strlen($matches[0]) - 2);
            return $this->createInstance($plugin_id, $configuration);
          }
          else {
            throw new PluginException("The '$plugin_id' plugin does not define a 'parens' property");
          }
        }
        else {
          throw new PluginNotFoundException($plugin_id);
        }
      }
    }
    return parent::createInstance($plugin_id, $configuration);
  }

}
