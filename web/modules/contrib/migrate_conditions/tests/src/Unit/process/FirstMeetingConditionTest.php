<?php

namespace Drupal\Tests\migrate_conditions\Unit\process;

use Drupal\migrate_conditions\Plugin\migrate\process\FirstMeetingCondition;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the first_meeting_condition process plugin.
 *
 * @group migrate_conditions
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\FirstMeetingCondition
 */
class FirstMeetingConditionTest extends MigrateProcessTestCase {

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestFirstMeetingCondition
   */
  public function testFirstMeetingCondition($value, $evaluate, $default_value, $expected) {
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
      'default_value' => $default_value,
    ];
    $transformed = (new FirstMeetingCondition($configuration, 'first_meeting_condition', [], $condition_manager))
      ->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($expected, $transformed);
  }

  /**
   * Data provider for ::testFirstMeetingCondition().
   */
  public function providerTestFirstMeetingCondition() {
    return [
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [TRUE],
        'default_value' => 'my default',
        'expected' => 'one',
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [FALSE, TRUE],
        'default_value' => 'my default',
        'expected' => 'two',
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [FALSE, FALSE, TRUE],
        'default_value' => 'my default',
        'expected' => 'three',
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [FALSE, FALSE, FALSE],
        'default_value' => 'my default',
        'expected' => 'my default',
      ],
      [
        'value' => 'one',
        'evaluate' => [TRUE],
        'default_value' => 'my default',
        'expected' => 'one',
      ],
      [
        'value' => 'one',
        'evaluate' => [FALSE],
        'default_value' => 'my default',
        'expected' => 'my default',
      ],
    ];
  }

}
