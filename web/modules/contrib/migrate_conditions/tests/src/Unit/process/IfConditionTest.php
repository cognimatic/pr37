<?php

namespace Drupal\Tests\migrate_conditions\Unit\process;

use Drupal\migrate_conditions\Plugin\migrate\process\IfCondition;
use Drupal\migrate_conditions\Plugin\ProcessPluginWithConditionBase;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the if_condition process plugin.
 *
 * @group migrate_conditions
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\IfCondition
 */
class IfConditionTest extends MigrateProcessTestCase {

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestIfCondition
   */
  public function testIfCondition($source, $evaluate, $expected, $do_get = [], $else_get = []) {
    $condition = $this->createMock('\Drupal\migrate_conditions\ConditionInterface');
    $condition->expects($this->once())
      ->method('evaluate')
      ->will($this->returnValue($evaluate));
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $map = [];
    if (!empty($do_get)) {
      $map[] = [$do_get['property'], $do_get['value']];
    }
    if (!empty($else_get)) {
      $map[] = [$else_get['property'], $else_get['value']];
    }
    if (!empty($map)) {
      $this->row
        ->method('get')
        ->willReturnMap($map);
    }

    $configuration = [
      'condition' => 'foo',
    ];
    if (!empty($do_get)) {
      $configuration['do_get'] = $do_get['property'];
    }
    if (!empty($else_get)) {
      $configuration['else_get'] = $else_get['property'];
    }
    $evaluated = (new IfCondition($configuration, 'if_condition', [], $condition_manager))
      ->transform($source, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($expected, $evaluated);
  }

  /**
   * Data provider for ::testIfCondition().
   */
  public function providerTestIfCondition() {
    return [
      [
        'source' => 123,
        'evaluate' => TRUE,
        'expected' => 123,
      ],
      [
        'source' => 123,
        'evaluate' => FALSE,
        'expected' => NULL,
      ],
      [
        'source' => 123,
        'evaluate' => TRUE,
        'expected' => 'my do',
        'do_get' => [
          'property' => 'some do get',
          'value' => 'my do',
        ],
        'else_get' => [
          'property' => 'else get property',
          'value' => 'else value',
        ],
      ],
      [
        'source' => 123,
        'evaluate' => FALSE,
        'expected' => 'else value',
        'do_get' => [
          'property' => 'some do get',
          'value' => 'my do',
        ],
        'else_get' => [
          'property' => 'else get property',
          'value' => 'else value',
        ],
      ],
    ];
  }

  /**
   * Tests configuration validation in constructor.
   *
   * @dataProvider providerTestConstructorValidation
   */
  public function testConstructorValidation($configuration, $message) {
    $condition = $this->createMock('\Drupal\migrate_conditions\ConditionInterface');
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->any())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $this->getMockForAbstractClass(IfCondition::class, [$configuration, 'just_a_base', [], $condition_manager]);
  }

  /**
   * Data provider for ::testConstructorValidation().
   */
  public function providerTestConstructorValidation() {
    return [
      [
        'configuration' => [
          'source' => 'my_source',
          'condition' => 'contains',
          'else_process' => 'not an array',
        ],
        'message' => "The 'else_process' configuration must be an array",
      ],
      [
        'configuration' => [
          'source' => 'my_source',
          'condition' => 'contains',
          'do_process' => 'not an array',
        ],
        'message' => "The 'do_process' configuration must be an array",
      ],
      [
        'configuration' => [
          'source' => 'my_source',
          'condition' => 'contains',
          'do_process' => [],
          'do_get' => 'something',
        ],
        'message' => "You may only set one of 'do_get' and 'do_process'.",
      ],
      [
        'configuration' => [
          'source' => 'my_source',
          'condition' => 'contains',
          'else_process' => [],
          'else_get' => 'something',
        ],
        'message' => "You may only set one of 'else_get' and 'else_process'.",
      ],
    ];
  }

}
