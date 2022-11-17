<?php

namespace Drupal\Tests\migrate_conditions\Kernel\Plugin\condition;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate_conditions\Plugin\migrate_conditions\condition\OlderThan;

/**
 * Tests the older_than condition plugin.
 *
 * @group migrate_conditions
 */
class OlderThanTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate', 'migrate_conditions'];

  /**
   * Tests validation in constructor.
   */
  public function testConstructor() {
    $configuration = [];
    $plugin_definition = \Drupal::service('plugin.manager.migrate_conditions.condition')->getDefinition('older_than');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The format configuration is required when using the older_than condition.');
    $condition = new OlderThan($configuration, 'older_than', $plugin_definition);
  }

}
