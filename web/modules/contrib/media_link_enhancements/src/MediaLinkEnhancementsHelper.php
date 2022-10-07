<?php

namespace Drupal\media_link_enhancements;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Service class for helper functions.
 *
 * Provides helper functions to determine if extensions and
 * bundles are valid.
 */
class MediaLinkEnhancementsHelper implements MediaLinkEnhancementsHelperInterface {

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a MediaLinkEnhancementsHelper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory instance.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory->get('media_link_enhancements.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function checkBundle($bundle, $config_key) {

    $allowed = $this->configFactory->get($config_key);
    if (empty($bundle) || empty($allowed) || !is_array($allowed)) {
      return FALSE;
    }

    if (in_array($bundle, $allowed) && !empty($allowed[$bundle])) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function checkExtension($extension, $config_key) {

    $allowed = $this->configFactory->get($config_key);
    if (empty($extension) || empty($allowed)) {
      return TRUE;
    }
    $allowed = explode(',', $allowed);
    $allowed = array_map('trim', $allowed);
    $allowed = array_map('strtoupper', $allowed);
    $allowed = array_unique($allowed);
    if (in_array(strtoupper($extension), $allowed)) {
      return TRUE;
    }

    return FALSE;
  }

}
