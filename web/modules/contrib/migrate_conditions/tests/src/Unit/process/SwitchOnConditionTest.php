<?php

namespace Drupal\Tests\migrate_conditions\Unit\process;

use Drupal\migrate_conditions\Plugin\migrate\process\SwitchOnCondition;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the switch_on_condition process plugin.
 *
 * @group migrate_conditions
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\SwitchOnCondition
 */
class SwitchOnConditionTest extends MigrateProcessTestCase {

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestSwitchOnCondition
   */
  public function testSwitchOnCondition($configuration, $source, $evaluate, $expected) {
    $condition = $this->createMock('\Drupal\migrate_conditions\ConditionInterface');
    $condition->expects($this->once())
      ->method('evaluate')
      ->will($this->returnValue($evaluate));
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $evaluated = (new SwitchOnCondition($configuration, 'switch_on_condition', [], $condition_manager))
      ->transform($source, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($expected, $evaluated);
  }

  /**
   * Data provider for ::testSwitchOnCondition().
   */
  public function providerTestSwitchOnCondition() {
    return [
      [
        'configuration' => [
          'cases' => [
            [
              'condition' => 'foo',
              'default_value' => 'bar',
            ],
          ],
        ],
        'source' => 123,
        'evaluate' => TRUE,
        'expected' => 'bar',
      ],
      [
        'configuration' => [
          'cases' => [
            [
              'condition' => 'foo',
              'default_value' => 'bar',
            ],
          ],
        ],
        'source' => 123,
        'evaluate' => FALSE,
        'expected' => NULL,
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
    $plugin = new SwitchOnCondition($configuration, 'switch_on_condition', [], $condition_manager);
  }

  /**
   * Data provider for ::testConstructorValidation().
   */
  public function providerTestConstructorValidation() {
    return [
      'no cases' => [
        'configuration' => [
          'source' => 'my_source',
        ],
        'message' => "The 'cases' configuration is required.",
      ],
      'cases not an array' => [
        'configuration' => [
          'source' => 'my_source',
          'cases' => 'some string',
        ],
        'message' => "The 'cases' configuration must be an array.",
      ],
      'cases an invalid array' => [
        'configuration' => [
          'source' => 'my_source',
          'cases' => [
            'not the right shape of array',
          ],
        ],
        'message' => "Each item in the 'cases' array must be an array",
      ],
      'no condition' => [
        'configuration' => [
          'source' => 'my_source',
          'cases' => [
            [
              'get' => 'something',
            ],
          ],
        ],
        'message' => "Each item in the 'cases' array must have a 'condition' configured.",
      ],
      'nothing to do' => [
        'configuration' => [
          'source' => 'my_source',
          'cases' => [
            [
              'condition' => 'empty',
            ],
          ],
        ],
        'message' => "Each item in the 'cases' must configure exactly one of 'get', 'process', and 'default_value'",
      ],
      'too much to do' => [
        'configuration' => [
          'source' => 'my_source',
          'cases' => [
            [
              'condition' => 'empty',
              'get' => 'something',
              'process' => 'something',
            ],
          ],
        ],
        'message' => "Each item in the 'cases' must configure exactly one of 'get', 'process', and 'default_value'",
      ],
      'bad get' => [
        'configuration' => [
          'source' => 'my_source',
          'cases' => [
            [
              'condition' => 'empty',
              'get' => ['array'],
            ],
          ],
        ],
        'message' => "The value of a case's 'get' property must be a string.",
      ],
      'bad process' => [
        'configuration' => [
          'source' => 'my_source',
          'cases' => [
            [
              'condition' => 'empty',
              'process' => 'string',
            ],
          ],
        ],
        'message' => "The value of a case's 'process' property must be an array.",
      ],
    ];
  }

}
