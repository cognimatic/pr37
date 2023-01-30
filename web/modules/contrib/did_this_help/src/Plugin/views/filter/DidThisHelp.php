<?php

namespace Drupal\did_this_help\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Filters by given list of yes/no options.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("did_this_help")
 */
class DidThisHelp extends InOperator {

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (isset($this->valueOptions)) {
      return $this->valueOptions;
    }

    $this->valueOptions = [
      'yes' => $this->t('Yes'),
      'no' => $this->t('No'),
    ];

    return $this->valueOptions;
  }

}
