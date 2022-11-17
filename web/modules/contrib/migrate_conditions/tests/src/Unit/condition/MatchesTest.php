<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

/**
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\Matches
 * @group migrate_conditions
 */
class MatchesTest extends ConditionTestBase {

  use ConditionTestValidationTrait;
  use ConditionTestEvaluateExceptionsTrait;

  /**
   * {@inheritdoc}
   */
  protected $conditionClass = 'Drupal\migrate_conditions\Plugin\migrate_conditions\condition\Matches';

  /**
   * {@inheritdoc}
   */
  protected $conditionId = 'matches';

  /**
   * Data provider for ::testConfigurationValidation().
   */
  public function providerTestConfigurationValidation() {
    return [
      [
        'configuration' => [
          'regex' => '123',
        ],
        'message' => 'The regex 123 is invalid.',
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
          'regex' => '/\d+/',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 'my 123 string',
        'configuration' => [
          'regex' => '/\d+/',
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
      'Source is not a string' => [
        'source' => 123,
        'configuration' => [
          'regex' => '/abc/',
        ],
        'property_value' => NULL,
        'expected' => 'When using the matches condition, the source must be a string.',
      ],
    ];
  }

}
