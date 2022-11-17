<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

use Drupal\migrate\MigrateException;

/**
 * Trait useful for some condition plugins.
 */
trait ConditionTestEvaluateExceptionsTrait {

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluateExceptions
   */
  public function testEvaluateExceptions($source, $configuration, $property_value, $expected_message) {
    $row = $this->createMock('Drupal\migrate\Row');
    if (isset($configuration['property'])) {
      $row->expects($this->any())
        ->method('get')
        ->with($configuration['property'])
        ->willReturn($property_value);
    }
    $class = $this->conditionClass;
    $condition = new $class($configuration, $this->conditionId, []);
    $this->expectException(MigrateException::class);
    $this->expectExceptionMessage($expected_message);
    $condition->evaluate($source, $row);
  }

  /**
   * Data provider for ::testEvaluateExceptions().
   */
  public function providerTestEvaluateExceptions() {
    return [];
  }

}
