<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

/**
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\Contains
 * @group migrate_conditions
 */
class ContainsTest extends ConditionTestBase {

  use ConditionTestValidationTrait;
  use ConditionTestEvaluateExceptionsTrait;

  /**
   * {@inheritdoc}
   */
  protected $conditionClass = 'Drupal\migrate_conditions\Plugin\migrate_conditions\condition\Contains';

  /**
   * {@inheritdoc}
   */
  protected $conditionId = 'contains';

  /**
   * Data provider for ::testConfigurationValidation().
   */
  public function providerTestConfigurationValidation() {
    return [
      [
        'configuration' => [
          'value' => 5,
          'property' => 'my_property',
        ],
        'message' => 'Exactly one of value and property must be set when using the contains condition.',
      ],
      [
        'configuration' => [
          'format' => 'U',
        ],
        'message' => 'Exactly one of value and property must be set when using the contains condition',
      ],
      [
        'configuration' => [
          'property' => 123,
        ],
        'message' => 'The property configuration must be a string when using the contains condition.',
      ],
    ];
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 'my string',
        'configuration' => [
          'value' => 'str',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => 'my string',
        'configuration' => [
          'value' => 'trs',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 'my string',
        'configuration' => [
          'property' => 'whatever',
        ],
        'property_value' => 'str',
        'expected' => TRUE,
      ],
      [
        'source' => 'my string',
        'configuration' => [
          'property' => 'whatever',
        ],
        'property_value' => 'trs',
        'expected' => FALSE,
      ],
      [
        'source' => ['one', 'two', 'three'],
        'configuration' => [
          'value' => 'two',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => ['one', 'two', 'three'],
        'configuration' => [
          'value' => 'four',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => ['one', 'two', 'three'],
        'configuration' => [
          'property' => 'whatever',
        ],
        'property_value' => 'two',
        'expected' => TRUE,
      ],
      [
        'source' => ['one', 'two', 'three'],
        'configuration' => [
          'property' => 'whatever',
        ],
        'property_value' => 'four',
        'expected' => FALSE,
      ],
    ];
  }

  /**
   * Data provider for ::testEvaluateExceptions().
   */
  public function providerTestEvaluateExceptions() {
    return [
      'Source string but value not a string.' => [
        'source' => 'my string',
        'configuration' => [
          'value' => 123,
        ],
        'property_value' => NULL,
        'expected' => 'When using the contains condition with a string source, the value/property must be a string.',
      ],
      'Source string but property not a string.' => [
        'source' => 'my string',
        'configuration' => [
          'property' => 'whatever',
        ],
        'property_value' => 123,
        'expected' => 'When using the contains condition with a string source, the value/property must be a string.',
      ],
      'Source is neither array nor string' => [
        'source' => 123,
        'configuration' => [
          'value' => 'whatever',
        ],
        'property_value' => NULL,
        'expected' => 'When using the contains condition the source must be an array or a string.',
      ],
    ];
  }

}
