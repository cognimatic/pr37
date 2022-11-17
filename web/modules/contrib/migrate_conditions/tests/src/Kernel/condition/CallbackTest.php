<?php

namespace Drupal\Tests\migrate_conditions\Kernel\Plugin\condition;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate_conditions\Plugin\migrate_conditions\condition\Callback;

/**
 * Tests the callback condition plugin.
 *
 * @group migrate_conditions
 */
class CallbackTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate', 'migrate_conditions'];

  /**
   * Tests validation in constructor.
   */
  public function testConstructor() {
    $configuration = [];
    $plugin_definition = \Drupal::service('plugin.manager.migrate_conditions.condition')->getDefinition('callback');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The callable configuration is required when using the callback condition.');
    $condition = new Callback($configuration, 'callback', $plugin_definition);
  }

}
