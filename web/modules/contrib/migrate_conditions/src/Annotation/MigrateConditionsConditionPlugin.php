<?php

namespace Drupal\migrate_conditions\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a migrate_conditions condition plugin annotation object.
 *
 * Plugin Namespace: Plugin\migrate_conditions\condition
 *
 * @Annotation
 */
class MigrateConditionsConditionPlugin extends Plugin {

  /**
   * A unique identifier for the condition plugin.
   *
   * @var string
   */
  public $id;

  /**
   * Array of required configuration keys.
   *
   * @var string[]
   */
  public $requires = [];

  /**
   * The configuration key that can be placed in parens with the plugin id.
   *
   * Any value passed through parens is interpreted as a string. Passing
   * integers, for example, is not possible. Any number will be cast as a
   * string.
   *
   * @var string
   */
  public $parens;

}
