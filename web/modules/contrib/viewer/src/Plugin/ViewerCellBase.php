<?php

namespace Drupal\viewer\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * ViewerCell plugin base class.
 *
 * @package viewer
 */
class ViewerCellBase extends PluginBase implements ViewerCellInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getApplicableViewers() {
    return $this->pluginDefinition['viewers'];
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $row) {
    return $value;
  }

}
