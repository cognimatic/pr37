<?php

namespace Drupal\Tests\migrate_conditions\Unit\process;

use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate_conditions\Plugin\migrate\process\SkipOnCondition;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the skip on condition process plugin.
 *
 * @group migrate_conditions
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\SkipOnCondition
 */
class SkipOnConditionTest extends MigrateProcessTestCase {

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
    $process = new SkipOnCondition($configuration, 'skip_on_condition', [], $condition_manager);
  }

  /**
   * Data provider for ::testConstructorValidation().
   */
  public function providerTestConstructorValidation() {
    return [
      'no method' => [
        'configuration' => [
          'condition' => 'foo',
        ],
        'message' => 'The "method" must be set to either "row" or "process".',
      ],
      'bad method' => [
        'configuration' => [
          'method' => 'invalid',
          'condition' => 'foo',
        ],
        'message' => 'The "method" must be set to either "row" or "process".',
      ],
      'incompatible message and message_context' => [
        'configuration' => [
          'method' => 'row',
          'condition' => 'foo',
          'message' => 'Something with three blanks %s %s %s',
          'message_context' => [
            'one',
            'two',
          ],
        ],
        'message' => 'The message and/or message_context configuration are invalid: 4 arguments are required, 3 given',
      ],
    ];
  }

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestSkipOnCondition
   */
  public function testSkipOnCondition($will_skip, $method, $evaluate, $message) {
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
      'method' => $method,
      'condition' => 'foo',
      'message' => $message,
    ];
    if ($will_skip) {
      if ($method === 'process') {
        $this->expectException(MigrateSkipProcessException::class);
      }
      else {
        $this->expectException(MigrateSkipRowException::class);
        $this->expectExceptionMessage($message);
      }
      (new SkipOnCondition($configuration, 'skip_on_condition', [], $condition_manager))
        ->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
    }
    else {
      $pass_through = (new SkipOnCondition($configuration, 'skip_on_condition', [], $condition_manager))
        ->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
      $this->assertSame($value, $pass_through);
    }
  }

  /**
   * Data provider for ::testSkipOnCondition().
   */
  public function providerTestSkipOnCondition() {
    return [
      'skip row no message' => [
        'will_skip' => TRUE,
        'method' => 'row',
        'evaluate' => TRUE,
        'message' => '',
      ],
      'skip row with message' => [
        'will_skip' => TRUE,
        'method' => 'row',
        'evaluate' => TRUE,
        'message' => 'My message',
      ],
      'skip process' => [
        'will_skip' => TRUE,
        'method' => 'process',
        'evaluate' => TRUE,
        'message' => '',
      ],
      'pass through row' => [
        'will_skip' => FALSE,
        'method' => 'row',
        'evaluate' => FALSE,
        'message' => '',
      ],
      'pass through process' => [
        'will_skip' => FALSE,
        'method' => 'process',
        'evaluate' => FALSE,
        'message' => '',
      ],
    ];
  }

  /**
   * Tests a skip row exception with a message and message_context string.
   *
   * @covers ::row
   */
  public function testSkipWithMessageContextString() {
    $condition = $this->createMock('\Drupal\migrate_conditions\ConditionInterface');
    $condition->expects($this->once())
      ->method('evaluate')
      ->will($this->returnValue(TRUE));
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $configuration = [
      'condition' => 'whatever',
      'method' => 'row',
      'message' => 'The condition is true and foo is %s',
      'message_context' => 'foo',
    ];
    $this->row->method('get')
      ->with('foo')
      ->willReturn(123);

    $process = new SkipOnCondition($configuration, 'skip_on_condition', [], $condition_manager);
    $this->expectException(MigrateSkipRowException::class);
    $this->expectExceptionMessage('The condition is true and foo is 123');
    $process->transform('anything', $this->migrateExecutable, $this->row, 'destination_property');
  }

  /**
   * Tests a skip row exception with a message and message_context array.
   *
   * @covers ::row
   */
  public function testSkipWithMessageContextArray() {
    $condition = $this->createMock('\Drupal\migrate_conditions\ConditionInterface');
    $condition->expects($this->once())
      ->method('evaluate')
      ->will($this->returnValue(TRUE));
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $configuration = [
      'condition' => 'whatever',
      'method' => 'row',
      'message' => 'The condition is true and foo is %s and bar is %s',
      'message_context' => [
        'foo',
        'bar',
      ],
    ];
    $this->row->method('get')
      ->willReturnMap([
        ['foo', 123],
        ['bar', 'migrate rules'],
      ]);

    $process = new SkipOnCondition($configuration, 'skip_on_condition', [], $condition_manager);
    $this->expectException(MigrateSkipRowException::class);
    $this->expectExceptionMessage('The condition is true and foo is 123 and bar is migrate rules');
    $process->transform('anything', $this->migrateExecutable, $this->row, 'destination_property');
  }

}
