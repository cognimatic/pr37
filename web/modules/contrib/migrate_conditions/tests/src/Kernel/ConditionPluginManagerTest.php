<?php

namespace Drupal\Tests\migrate_conditions\Kernel;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Tests the ConditionPluginManager.
 *
 * @group migrate_conditions
 */
class ConditionPluginManagerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate', 'migrate_conditions'];

  /**
   * Tests using 'not:' to negate plugins.
   */
  public function testConditionPluginManagerNot() {
    $row = $this->createMock('Drupal\migrate\Row');

    // Typical scenario.
    $configuration = [
      'value' => 123,
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('not:equals', $configuration);
    $this->assertTrue($condition->evaluate(234, $row));
    $this->assertFalse($condition->evaluate(123, $row));

    // Explicitly setting negate to false and using 'not:'.
    $configuration = [
      'value' => 123,
      'negate' => FALSE,
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('not:equals', $configuration);
    $this->assertTrue($condition->evaluate(234, $row));
    $this->assertFalse($condition->evaluate(123, $row));

    // Two 'not:' prefixes.
    $configuration = [
      'value' => 123,
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('not:not:equals', $configuration);
    $this->assertFalse($condition->evaluate(234, $row));
    $this->assertTrue($condition->evaluate(123, $row));

    // Passing 'negate' config and using 'not:'.
    $configuration = [
      'value' => 123,
      'negate' => TRUE,
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('not:equals', $configuration);
    $this->assertFalse($condition->evaluate(234, $row));
    $this->assertTrue($condition->evaluate(123, $row));
  }

  /**
   * Tests using parens to pass values to plugins.
   */
  public function testConditionPluginManagerParens() {
    $row = $this->createMock('Drupal\migrate\Row');

    // Test with equals.
    $configuration = [];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('equals(123)', $configuration);
    $this->assertTrue($condition->evaluate(123, $row));
    $this->assertTrue($condition->evaluate('123', $row));
    $this->assertFalse($condition->evaluate(321, $row));
    $this->assertFalse($condition->evaluate('321', $row));

    // Test with not: prefix.
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('not:equals(123)', $configuration);
    $this->assertFalse($condition->evaluate(123, $row));
    $this->assertFalse($condition->evaluate('123', $row));
    $this->assertTrue($condition->evaluate(321, $row));
    $this->assertTrue($condition->evaluate('321', $row));

    // Test with double parens.
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('equals(123)(321)', $configuration);
    $this->assertFalse($condition->evaluate(123, $row));
    $this->assertFalse($condition->evaluate(321, $row));
    $this->assertTrue($condition->evaluate('123)(321', $row));

    // Test with a plugin that does not declare parens.
    $message = '';
    try {
      $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('and(123)', $configuration);
    }
    catch (PluginException $e) {
      $message = $e->getMessage();
    }
    $expected = "The 'and' plugin does not define a 'parens' property";
    $this->assertSame($expected, $message);

    // Test with a plugin that does not exist.
    $message = '';
    try {
      $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('fake(123)', $configuration);
    }
    catch (PluginNotFoundException $e) {
      $message = $e->getMessage();
    }
    $expected = "Plugin ID 'fake' was not found.";
    $this->assertSame($expected, $message);

    // Test with empty parens.
    $message = '';
    try {
      $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('equals()', $configuration);
    }
    catch (PluginException $e) {
      $message = $e->getMessage();
    }
    $expected = 'The "equals()" plugin does not exist';
    $this->assertStringContainsString($expected, $message);
  }

}
