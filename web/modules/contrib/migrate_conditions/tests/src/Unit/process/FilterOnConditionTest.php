<?php

namespace Drupal\Tests\migrate_conditions\Unit\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate_conditions\Plugin\migrate\process\FilterOnCondition;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the filter_on_condition process plugin.
 *
 * @group migrate_conditions
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\FilterOnCondition
 */
class FilterOnConditionTest extends MigrateProcessTestCase {

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestFilterOnCondition
   */
  public function testFilterOnCondition($value, $evaluate, $expected, $preserve_keys = NULL) {
    $condition = $this->createMock('\Drupal\migrate_conditions\ConditionInterface');
    $condition->expects($this->exactly(count($evaluate)))
      ->method('evaluate')
      ->willReturnOnConsecutiveCalls(...$evaluate);
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $configuration = [
      'condition' => 'foo',
    ];
    if (!is_null($preserve_keys)) {
      $configuration['preserve_keys'] = $preserve_keys;
    }
    $transformed = (new FilterOnCondition($configuration, 'filter_on_condition', [], $condition_manager))
      ->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($expected, $transformed);
  }

  /**
   * Data provider for ::testFilterOnCondition().
   */
  public function providerTestFilterOnCondition() {
    return [
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [TRUE, TRUE, TRUE],
        'expected' => ['one', 'two', 'three'],
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [TRUE, FALSE, TRUE],
        'expected' => [0 => 'one', 1 => 'three'],
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [TRUE, FALSE, TRUE],
        'expected' => [0 => 'one', 1 => 'three'],
        'preserve_keys' => FALSE,
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [TRUE, FALSE, TRUE],
        'expected' => [0 => 'one', 2 => 'three'],
        'preserve_keys' => TRUE,
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [FALSE, FALSE, FALSE],
        'expected' => [],
      ],
    ];
  }

  /**
   * Tests input validation.
   */
  public function testFilterOnConditionNotArray() {
    $condition = $this->createMock('\Drupal\migrate_conditions\ConditionInterface');
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $configuration = [
      'condition' => 'foo',
    ];
    $this->expectException(MigrateException::class);
    $this->expectExceptionMessage('The input value should be an array.');
    (new FilterOnCondition($configuration, 'filter_on_condition', [], $condition_manager))
      ->transform('', $this->migrateExecutable, $this->row, 'destination_property');
  }

}
