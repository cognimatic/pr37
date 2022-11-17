<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ConditionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'in_migrate_map' condition.
 *
 * This functions similarly to the migration_lookup process plugin with a
 * couple notable differences. First, this is a migrate_condition plugin, so
 * it returns a boolean rather than destination ids. Second, this plugin will
 * never create stubs and as such there is not a no_stub option. Third, the
 * treatment of skipped rows is configurable through the include_skipped
 * property.
 *
 * Available configuration keys:
 * - migration: The migration id or array of migration ids against which to do
 *   a lookup.
 * - include_skipped: (optional) If TRUE, a source is considered to be in the
 *   map even the row has been skipped and the destination ids are null.
 *   Defaults to FALSE.
 * - negate: (optional) Whether the 'in_migrate_map' condition should be
 *   negated. Defaults to FALSE. You can also negate the 'in_migrate_map'
 *   plugin by using 'not:in_migrate_map' as the plugin id.
 *
 * Examples:
 *
 * Skip the row if a value is in a certain migrate map.
 *
 * @code
 * process:
 *   _skip_if_in_other_migration:
 *     plugin: skip_on_condition
 *     method: row
 *     source: my_source_value
 *     condition:
 *       plugin: in_migrate_map
 *       migrate: some_other_migration
 * @endcode
 *
 * or equivalently
 *
 * @code
 * process:
 *   _skip_if_in_other_migration:
 *     plugin: skip_on_condition
 *     method: row
 *     source: my_source_value
 *     condition: in_migrate_map(some_other_migration)
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "in_migrate_map",
 *   requires = {"migration"},
 *   parens = "migration"
 * )
 */
class InMigrateMap extends ConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The migrate lookup service.
   *
   * @var \Drupal\migrate\MigrateLookupInterface
   */
  protected $migrateLookup;

  /**
   * InMigrateMap constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\migrate\MigrateLookupInterface $migrate_lookup
   *   The migrate lookup service.
   * @param Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_plugin_manager
   *   The migration plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrateLookupInterface $migrate_lookup, MigrationPluginManagerInterface $migration_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration['include_skipped'] = $configuration['include_skipped'] ?? FALSE;
    $this->configuration['migration'] = (array) $configuration['migration'];
    foreach ($this->configuration['migration']  as $migration_id) {
      $migration = $migration_plugin_manager->createInstance($migration_id);
      if (empty($migration)) {
        throw new \InvalidArgumentException('The migration configured for in_migrate_map could not be loaded: ' . $migration_id);
      }
    }
    $this->migrateLookup = $migrate_lookup;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('migrate.lookup'),
      $container->get('plugin.manager.migration')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    $lookup = $this->migrateLookup->lookup($this->configuration['migration'], (array) $source);
    // If the source was not found, an empty array is returned.
    if (empty($lookup)) {
      return FALSE;
    }
    // If the source was found but the row was skipped, the lookup
    // will be an array of arrays containing at least one NULL id.
    if ($this->configuration['include_skipped'] === TRUE) {
      return TRUE;
    }
    else {
      foreach ($lookup as $ids) {
        foreach ($ids as $id) {
          if (is_null($id)) {
            return FALSE;
          }
        }
      }
      return TRUE;
    }
  }

}
