<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

/**
 * Trait useful for some condition plugins.
 */
trait ConditionTestValidationTrait {

  /**
   * @covers ::__construct()
   * @dataProvider providerTestConfigurationValidation
   */
  public function testConfigurationValidation($configuration, $message) {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $class = $this->conditionClass;
    $condition = new $class($configuration, $this->conditionId, []);
  }

  /**
   * Data provider for ::testConfigurationValidation().
   */
  public function providerTestConfigurationValidation() {
    return [];
  }

}
