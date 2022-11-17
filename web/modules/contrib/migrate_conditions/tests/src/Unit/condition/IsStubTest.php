<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

use Drupal\migrate_conditions\Plugin\migrate_conditions\condition\IsStub;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\IsStub
 * @group migrate_conditions
 */
class IsStubTest extends UnitTestCase {

  /**
   * @covers ::evaluate
   */
  public function testEvaluate() {
    $row = $this->createMock('Drupal\migrate\Row');

    // Row is a stub.
    $row->expects($this->any())
      ->method('isStub')
      ->willReturn(TRUE);
    $condition = new IsStub([], 'is_stub', []);
    $this->assertTrue($condition->evaluate(NULL, $row));

    // Row is not a stub.
    $row = $this->createMock('Drupal\migrate\Row');
    $row->expects($this->any())
      ->method('isStub')
      ->willReturn(FALSE);
    $this->assertFalse($condition->evaluate(NULL, $row));

    // Negate and expect the opposite.
    $negated_condition = new IsStub(['negate' => TRUE], 'is_stub', []);
    $this->assertTrue($negated_condition->evaluate(NULL, $row));
  }

}
