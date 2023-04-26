<?php

namespace Drupal\viewer\Commands;

use Drush\Commands\DrushCommands;
use Drupal\viewer\Services\Cron;

/**
 * ViewerCommands drush commands.
 */
class ViewerCommands extends DrushCommands {

  /**
   * Cron service.
   *
   * @var \Drupal\viewer\Services\Cron
   */
  protected $cron;

  /**
   * Constructor.
   *
   * @param \Drupal\viewer\Services\Cron $cron
   *   Cron service.
   */
  public function __construct(Cron $cron) {
    parent::__construct();
    $this->cron = $cron;
  }

  /**
   * Drush command to run Viewer automated imports.
   *
   * @command viewer:import
   * @aliases vimp
   * @usage vimp
   */
  public function import() {
    $this->cron->processQueue();
  }

}
