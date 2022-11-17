<?php

namespace Drupal\Tests\migrate_conditions\Kernel\Plugin\condition;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the 'all_elements' condition plugin.
 *
 * @group migrate_conditions
 */
class AllElementsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate', 'migrate_conditions'];

  /**
   * Tests evaluating the 'all_elements' condition.
   */
  public function testEvaluate() {
    $row = $this->createMock('Drupal\migrate\Row');
    $configuration = [
      'condition' => [
        'plugin' => 'greater_than',
        'value' => 0,
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('all_elements', $configuration);

    $this->assertTrue($condition->evaluate([1, 2, 3], $row));
    $this->assertTrue($condition->evaluate(2, $row));
    $this->assertFalse($condition->evaluate([1, 3, -4], $row));
    $this->assertFalse($condition->evaluate([], $row));
  }

}
