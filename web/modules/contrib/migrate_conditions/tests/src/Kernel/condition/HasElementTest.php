<?php

namespace Drupal\Tests\migrate_conditions\Kernel\Plugin\condition;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the 'has_element' condition plugin.
 *
 * @group migrate_conditions
 */
class HasElementTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate', 'migrate_conditions'];

  /**
   * Tests evaluating the 'has_element' condition.
   */
  public function testEvaluate() {
    $row = $this->createMock('Drupal\migrate\Row');
    $configuration = [
      'condition' => [
        'plugin' => 'equals',
        'value' => 2,
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('has_element', $configuration);

    $this->assertTrue($condition->evaluate([1, 2, 3], $row));
    $this->assertTrue($condition->evaluate(2, $row));
    $this->assertFalse($condition->evaluate([1, 3, 4], $row));
    $this->assertFalse($condition->evaluate([], $row));

    // Set the index configuration.
    $configuration = [
      'index' => 1,
      'condition' => [
        'plugin' => 'equals',
        'value' => 2,
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('has_element', $configuration);

    $this->assertTrue($condition->evaluate([1, 2, 3], $row));
    $this->assertFalse($condition->evaluate(2, $row));
    $this->assertFalse($condition->evaluate([1, 3, 4], $row));
    $this->assertFalse($condition->evaluate([], $row));

    $configuration = [
      'index' => 'two',
      'condition' => [
        'plugin' => 'equals',
        'value' => 2,
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('has_element', $configuration);

    $this->assertTrue($condition->evaluate([
      'one' => 1,
      'two' => 2,
      'three' => 3,
    ], $row));
    $this->assertFalse($condition->evaluate(2, $row));
    $this->assertFalse($condition->evaluate([
      'one' => 1,
      'three' => 3,
      'four' => 4,
    ], $row));
    $this->assertFalse($condition->evaluate([
      'one' => 1,
      'two' => 'too',
      'three' => 3,
    ], $row));
    $this->assertFalse($condition->evaluate([], $row));
  }

}
