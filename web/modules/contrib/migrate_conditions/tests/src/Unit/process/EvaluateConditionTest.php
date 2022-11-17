<?php

namespace Drupal\Tests\migrate_conditions\Unit\process;

use Drupal\migrate_conditions\Plugin\migrate\process\EvaluateCondition;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the evaluate_condition process plugin.
 *
 * @group migrate_conditions
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\EvaluateCondition
 */
class EvaluateConditionTest extends MigrateProcessTestCase {

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestEvaluateCondition
   */
  public function testEvaluateCondition($evaluate, $expected) {
    $condition = $this->createMock('\Drupal\migrate_conditions\ConditionInterface');
    $condition->expects($this->once())
      ->method('evaluate')
      ->will($this->returnValue($evaluate));
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $value = 123;
    $configuration = [
      'condition' => 'foo',
    ];
    $evaluated = (new EvaluateCondition($configuration, 'evaluate_condition', [], $condition_manager))
      ->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($expected, $evaluated);
  }

  /**
   * Data provider for ::testEvaluateCondition().
   */
  public function providerTestEvaluateCondition() {
    return [
      'true' => [
        'evaluate' => TRUE,
        'expected' => TRUE,
      ],
      'false' => [
        'evaluate' => FALSE,
        'expected' => FALSE,
      ],
    ];
  }

}
