<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

/**
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\DefaultCondition
 * @group migrate_conditions
 */
class DefaultConditionTest extends ConditionTestBase {

  /**
   * {@inheritdoc}
   */
  protected $conditionClass = 'Drupal\migrate_conditions\Plugin\migrate_conditions\condition\DefaultCondition';

  /**
   * {@inheritdoc}
   */
  protected $conditionId = 'default';

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 123,
        'configuration' => [],
        'property_value' => 567,
        'expected' => TRUE,
      ],
      [
        'source' => NULL,
        'configuration' => [],
        'property_value' => 567,
        'expected' => TRUE,
      ],
    ];
  }

}
