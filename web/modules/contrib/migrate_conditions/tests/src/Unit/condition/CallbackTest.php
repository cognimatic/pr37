<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;


/**
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\Callback
 * @group migrate_conditions
 */
class CallbackTest extends ConditionTestBase {

  use ConditionTestValidationTrait;
  use ConditionTestEvaluateExceptionsTrait;

  /**
   * {@inheritdoc}
   */
  protected $conditionClass = 'Drupal\migrate_conditions\Plugin\migrate_conditions\condition\Callback';

  /**
   * {@inheritdoc}
   */
  protected $conditionId = 'callback';

  /**
   * Data provider for ::testConfigurationValidation().
   */
  public function providerTestConfigurationValidation() {
    return [
      [
        'configuration' => [
          'callable' => 123,
        ],
        'message' => 'The "callable" must be a valid function or method.',
      ],
    ];
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => NULL,
        'configuration' => [
          'callable' => 'is_null',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => 'something',
        'configuration' => [
          'callable' => 'is_null',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => [
          'my string',
          's',
        ],
        'configuration' => [
          'callable' => 'strpos',
          'unpack_source' => TRUE,
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => [
          'my string',
          'x',
        ],
        'configuration' => [
          'callable' => 'strpos',
          'unpack_source' => TRUE,
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => [
          'my string',
          'm',
        ],
        'configuration' => [
          'callable' => 'strpos',
          'unpack_source' => TRUE,
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => [
          'my string',
          'x',
        ],
        'configuration' => [
          'callable' => 'strpos',
          'unpack_source' => TRUE,
          'strict' => FALSE,
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => [
          'my string',
          'm',
        ],
        'configuration' => [
          'callable' => 'strpos',
          'unpack_source' => TRUE,
          'strict' => TRUE,
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
    ];
  }

  /**
   * Data provider for ::testEvaluateExceptions().
   */
  public function providerTestEvaluateExceptions() {
    return [
      [
        'source' => 'not an array',
        'configuration' => [
          'callable' => 'str_replace',
          'unpack_source' => TRUE,
        ],
        'property_value' => NULL,
        'expected_message' => "When 'unpack_source' is set, the source must be an array. Instead it was of type 'string'",
      ],
    ];
  }

}
