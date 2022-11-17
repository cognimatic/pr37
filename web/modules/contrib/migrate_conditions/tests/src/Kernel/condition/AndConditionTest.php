<?php

namespace Drupal\Tests\migrate_conditions\Kernel\Plugin\condition;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the and condition plugin.
 *
 * @group migrate_conditions
 */
class AndConditionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate', 'migrate_conditions'];

  /**
   * Tests evaluating the and condition.
   */
  public function testEvaluate() {
    $row = $this->createMock('Drupal\migrate\Row');
    $configuration = [
      'conditions' => [
        [
          'plugin' => 'in_array',
          'value' => [1, 2, 3],
        ],
        [
          'plugin' => 'equals',
          'negate' => TRUE,
          'value' => 3,
        ],
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('and', $configuration);

    $this->assertTrue($condition->evaluate(1, $row));
    $this->assertTrue($condition->evaluate(2, $row));
    $this->assertFalse($condition->evaluate(3, $row));
    $this->assertFalse($condition->evaluate(4, $row));

    // Set iterate to false and assert the same results.
    $row = $this->createMock('Drupal\migrate\Row');
    $configuration = [
      'iterate' => FALSE,
      'conditions' => [
        [
          'plugin' => 'in_array',
          'value' => [1, 2, 3],
        ],
        [
          'plugin' => 'equals',
          'negate' => TRUE,
          'value' => 3,
        ],
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('and', $configuration);

    $this->assertTrue($condition->evaluate(1, $row));
    $this->assertTrue($condition->evaluate(2, $row));
    $this->assertFalse($condition->evaluate(3, $row));
    $this->assertFalse($condition->evaluate(4, $row));

    // Set iterate to true with numerical keys.
    $configuration = [
      'iterate' => TRUE,
      'conditions' => [
        [
          'plugin' => 'equals',
          'value' => 2,
        ],
        [
          'plugin' => 'equals',
          'value' => 3,
        ],
        [
          'plugin' => 'is_null',
        ],
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('and', $configuration);

    $this->assertTrue($condition->evaluate([2, 3, NULL], $row));
    $this->assertTrue($condition->evaluate([2, 3], $row));
    $this->assertTrue($condition->evaluate([2, 3, NULL, 'whatever'], $row));
    $this->assertFalse($condition->evaluate([3, 2], $row));

    // Set iterate to true with string keys.
    $configuration = [
      'iterate' => TRUE,
      'conditions' => [
        'first' => [
          'plugin' => 'equals',
          'value' => 2,
        ],
        'second' => [
          'plugin' => 'equals',
          'value' => 3,
        ],
        'third' => [
          'plugin' => 'is_null',
        ],
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('and', $configuration);

    $this->assertTrue($condition->evaluate([
      'first' => 2,
      'second' => 3,
      'third' => NULL,
    ], $row));
    $this->assertTrue($condition->evaluate([
      'first' => 2,
      'second' => 3,
    ], $row));
    $this->assertTrue($condition->evaluate([
      'second' => 3,
      'first' => 2,
    ], $row));
    $this->assertTrue($condition->evaluate([
      'first' => 2,
      'second' => 3,
      'fourth' => 'whatever',
    ], $row));
    $this->assertFalse($condition->evaluate([
      'first' => 3,
      'second' => 2,
    ], $row));
  }

}
