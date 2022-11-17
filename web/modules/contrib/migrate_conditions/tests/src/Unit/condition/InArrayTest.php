<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

/**
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\InArray
 * @group migrate_conditions
 */
class InArrayTest extends ConditionTestBase {

  use ConditionTestValidationTrait;

  /**
   * {@inheritdoc}
   */
  protected $conditionClass = 'Drupal\migrate_conditions\Plugin\migrate_conditions\condition\InArray';

  /**
   * {@inheritdoc}
   */
  protected $conditionId = 'in_array';

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
        'message' => 'Exactly one of value and property must be set when using the in_array condition.',
      ],
      [
        'configuration' => [],
        'message' => 'Exactly one of value and property must be set when using the in_array condition.',
      ],
      [
        'configuration' => [
          'property' => 123,
        ],
        'message' => 'The property configuration must be a string when using the in_array condition.',
      ],
    ];
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 'string',
        'configuration' => [
          'value' => 'string',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => 'something else',
        'configuration' => [
          'value' => 'string',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 2,
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => 4,
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => [1, 3, 3],
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'value' => [1, 2, [1, 2, 3]],
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => 2,
        'configuration' => [
          'property' => 'my_property',
        ],
        'property_value' => [1, 2, 3],
        'expected' => TRUE,
      ],
      [
        'source' => 4,
        'configuration' => [
          'property' => 'my_property',
        ],
        'property_value' => [1, 2, 3],
        'expected' => FALSE,
      ],
      [
        'source' => 2,
        'configuration' => [
          'property' => 'my_property',
        ],
        'property_value' => 2,
        'expected' => TRUE,
      ],
      [
        'source' => '2',
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => '2',
        'configuration' => [
          'value' => [1, 2, 3],
          'strict' => TRUE,
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 'something',
        'configuration' => [
          'value' => ['key' => 'something'],
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => 'key',
        'configuration' => [
          'value' => ['key' => 'something'],
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
    ];
  }

}
