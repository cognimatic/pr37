<?php

namespace Drupal\Tests\migrate_conditions\Unit\process;

use Drupal\migrate_conditions\Plugin\migrate_conditions\condition\Equals;
use Drupal\migrate_conditions\Plugin\ProcessPluginWithConditionBase;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the constructor of the ProcessPluginWithConditionBase.
 *
 * @group migrate_conditions
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\ProcessPluginWithConditionBase
 */
class ProcessPluginWithConditionBaseTest extends MigrateProcessTestCase {

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
    $this->getMockForAbstractClass(ProcessPluginWithConditionBase::class, [$configuration, 'just_a_base', [], $condition_manager]);
  }

  /**
   * Data provider for ::testConstructorValidation().
   */
  public function providerTestConstructorValidation() {
    return [
      [
        'configuration' => [],
        'message' => "The 'condition' must be set.",
      ],
      [
        'configuration' => [
          'condition' => [
            'foo' => 'bar',
          ],
        ],
        'message' => "The 'plugin' must be set for the condition.",
      ],
      [
        'configuration' => [
          'condition' => 123,
        ],
        'message' => "The 'condition' must be either a string or an array.",
      ],
    ];
  }

  /**
   * Tests condition instance created by process constructor.
   */
  public function testConditionInstance() {
    $equals_configuration = ['value' => 123];
    $equals = new Equals($equals_configuration, 'equals', []);
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->willReturnMap([['equals', ['value' => 123], $equals]]);

    $configuration = [
      'condition' => [
        'plugin' => 'equals',
        'value' => 123,
      ],
    ];
    $process = new ProcessPluginWithConditionBaseTestClass($configuration, 'test', [], $condition_manager);
    $condition = $process->getCondition();
    $this->assertSame('equals', $condition->getPluginId());
    $this->assertTrue($condition->evaluate(123, $this->row));
    $this->assertFalse($condition->evaluate(321, $this->row));
  }

}

/**
 * A test class so we can get a protected property.
 */
class ProcessPluginWithConditionBaseTestClass extends ProcessPluginWithConditionBase {

  /**
   * Helper function to get a protected property.
   */
  public function getCondition() {
    return $this->condition;
  }

}
