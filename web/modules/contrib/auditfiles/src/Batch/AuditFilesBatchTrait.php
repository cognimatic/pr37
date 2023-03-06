<?php

declare(strict_types=1);

namespace Drupal\auditfiles\Batch;

use Drupal\Core\Messenger\MessengerInterface;

/**
 * Batch trait.
 */
trait AuditFilesBatchTrait {

  /**
   * Finalize batch.
   */
  public static function finishBatch($success, $results, $operations) {
    if (!$success) {
      $error_operation = reset($operations);
      static::getMessenger()->addError(\t('An error occurred while processing @operation with arguments : @args', [
        '@operation' => $error_operation[0],
        '@args' => print_r($error_operation[0], TRUE),
      ]));
    }
  }

  /**
   * The messenger service.
   */
  protected static function getMessenger(): MessengerInterface {
    return \Drupal::service('messenger');
  }

}
