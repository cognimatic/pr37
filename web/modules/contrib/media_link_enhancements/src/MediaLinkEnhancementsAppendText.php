<?php

namespace Drupal\media_link_enhancements;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\Markup;
use Drupal\file\Entity\File;

/**
 * Service class for the append text functions.
 *
 * Provides file type and size text to append to media links.
 */
class MediaLinkEnhancementsAppendText implements MediaLinkEnhancementsAppendTextInterface {

  /**
   * Byte units for file size display.
   *
   * @var array
   */
  const BYTE_UNITS = ['b', 'kb', 'mb', 'gb', 'tb', 'pb', 'eb', 'zb', 'yb'];

  /**
   * Byte precision for file size display.
   *
   * @var array
   */
  const BYTE_PRECISION = [0, 0, 1, 2, 2, 3, 3, 4, 4];

  /**
   * Byte next for file size display.
   *
   * @var int
   */
  const BYTE_NEXT = 1024;

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Helper service.
   *
   * @var \Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface
   */
  protected $helper;

  /**
   * Constructs a MediaLinkEnhancementsAppendText object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory instance.
   * @param \Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface $helper
   *   Helper service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MediaLinkEnhancementsHelperInterface $helper) {
    $this->configFactory = $config_factory->get('media_link_enhancements.settings');
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public function getText($source) {

    // Bail if the source isn't a file object.
    if (!$source instanceof File) {
      return FALSE;
    }

    $filename = $source->getFileName();
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!$this->helper->checkExtension($extension, 'type_size_appending_extensions')) {
      return FALSE;
    }

    $size = $source->getSize();

    $prefix = $this->configFactory->get('type_size_appending_prefix');
    $separator = $this->configFactory->get('type_size_appending_separator');
    $suffix = $this->configFactory->get('type_size_appending_suffix');

    $text = ' ' . $prefix . $extension . $separator . $this->humanReadableBytes($size) . $suffix;

    if (!empty($this->configFactory->get('type_size_appending_uppercase'))) {
      $text = strtoupper($text);
    }

    return Markup::create($text)->__toString();
  }

  /**
   * Convert bytes to be human readable.
   *
   * @param int $bytes
   *   Bytes to make readable.
   * @param int|null $precision
   *   Precision of rounding.
   *
   * @return string
   *   Human readable bytes.
   */
  public static function humanReadableBytes($bytes, $precision = NULL) {
    $bytes = (int) $bytes;
    if (empty($bytes)) {
      return NULL;
    }
    for ($i = 0; ($bytes / static::BYTE_NEXT) >= 0.9 && $i < count(static::BYTE_UNITS); $i++) {
      $bytes /= static::BYTE_NEXT;
    }
    return round($bytes, is_null($precision) ? static::BYTE_PRECISION[$i] : $precision) . static::BYTE_UNITS[$i];
  }

}
