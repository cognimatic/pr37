<?php

namespace Drupal\Tests\migrate_conditions\Kernel\Plugin\condition;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the or condition plugin.
 *
 * @group migrate_conditions
 */
class OrConditionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate', 'migrate_conditions'];

  /**
   * Tests evaluating the or condition.
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
          'plugin' => 'greater_than',
          'negate' => TRUE,
          'value' => 2,
        ],
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('or', $configuration);

    $this->assertTrue($condition->evaluate(0, $row));
    $this->assertTrue($condition->evaluate(1, $row));
    $this->assertTrue($condition->evaluate(2, $row));
    $this->assertTrue($condition->evaluate(3, $row));
    $this->assertFalse($condition->evaluate(4, $row));

    // Set iterate to false and assert the same results.
    $configuration = [
      'iterate' => FALSE,
      'conditions' => [
        [
          'plugin' => 'in_array',
          'value' => [1, 2, 3],
        ],
        [
          'plugin' => 'greater_than',
          'negate' => TRUE,
          'value' => 2,
        ],
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('or', $configuration);

    $this->assertTrue($condition->evaluate(0, $row));
    $this->assertTrue($condition->evaluate(1, $row));
    $this->assertTrue($condition->evaluate(2, $row));
    $this->assertTrue($condition->evaluate(3, $row));
    $this->assertFalse($condition->evaluate(4, $row));

    // Set iterate to true with numerical indices.
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
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('or', $configuration);

    $this->assertTrue($condition->evaluate([2, 3, NULL], $row));
    $this->assertTrue($condition->evaluate([4, 6], $row));
    $this->assertTrue($condition->evaluate([5, 3, 6], $row));
    $this->assertFalse($condition->evaluate([0, 0, 3], $row));
    $this->assertTrue($condition->evaluate(['whatever'], $row));

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
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('or', $configuration);

    $this->assertTrue($condition->evaluate([
      'first' => 2,
      'second' => 3,
      'third' => NULL,
    ], $row));
    $this->assertTrue($condition->evaluate([
      'first' => 3,
      'second' => 2,
    ], $row));
    $this->assertFalse($condition->evaluate([
      'second' => 0,
      'first' => 0,
      'third' => 3,
    ], $row));
    $this->assertTrue($condition->evaluate([
      'fourth' => 'whatever',
    ], $row));
  }

}
