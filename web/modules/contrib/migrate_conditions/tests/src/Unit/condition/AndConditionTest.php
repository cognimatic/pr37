<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

/**
 * Tests the and condition plugin.
 *
 * @group migrate_conditions
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\AndCondition
 */
class AndConditionTest extends LogicalConditionTestBase {

  /**
   * {@inheritdoc}
   */
  protected $conditionClass = 'Drupal\migrate_conditions\Plugin\migrate_conditions\condition\AndCondition';

  /**
   * {@inheritdoc}
   */
  protected $conditionId = 'and';

  /**
   * Data provider for ::testConfigurationValidation().
   */
  public function providerTestConfigurationValidation() {
    return [
      [
        'configuration' => [],
        'message' => "The and condition requires 'conditions' be passed as configuration.",
      ],
      [
        'configuration' => [
          'conditions' => 'a string',
        ],
        'message' => "The 'conditions' passed to the and condition must be an array or arrays.",
      ],
      [
        'configuration' => [
          'conditions' => [
            'a string',
          ],
        ],
        'message' => "The 'conditions' passed to the and condition must be an array or arrays.",
      ],
      [
        'configuration' => [
          'conditions' => [
            [
              'plugin' => 'something',
            ],
            [
              'no_plugin' => 'uh oh',
            ],
          ],
        ],
        'message' => "Each condition element passed to the and condition must have the 'plugin' set.",
      ],
    ];
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 123,
        'evaluates' => [
          TRUE,
          TRUE,
          TRUE,
        ],
        'configuration' => [
          'conditions' => [
            [
              'plugin' => 'foo',
            ],
            [
              'plugin' => 'foo',
            ],
            [
              'plugin' => 'foo',
            ],
          ],
        ],
        'expected' => TRUE,
      ],
      [
        'source' => 123,
        'evaluates' => [
          FALSE,
          FALSE,
          FALSE,
        ],
        'configuration' => [
          'conditions' => [
            [
              'plugin' => 'foo',
            ],
            [
              'plugin' => 'foo',
            ],
            [
              'plugin' => 'foo',
            ],
          ],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => 123,
        'evaluates' => [
          TRUE,
          FALSE,
          TRUE,
        ],
        'configuration' => [
          'conditions' => [
            [
              'plugin' => 'foo',
            ],
            [
              'plugin' => 'foo',
            ],
            [
              'plugin' => 'foo',
            ],
          ],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => 123,
        'evaluates' => [
          TRUE,
        ],
        'configuration' => [
          'conditions' => [
            [
              'plugin' => 'foo',
            ],
          ],
        ],
        'expected' => TRUE,
      ],
      [
        'source' => 123,
        'evaluates' => [
          FALSE,
        ],
        'configuration' => [
          'conditions' => [
            [
              'plugin' => 'foo',
            ],
          ],
        ],
        'expected' => FALSE,
      ],
    ];
  }

}
