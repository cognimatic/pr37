<?php

namespace Drupal\media_link_enhancements;

/**
 * Interface for the class that provides link append text.
 */
interface MediaLinkEnhancementsAppendTextInterface {

  /**
   * Provides append text for applicable media links.
   *
   * @param mixed $source
   *   The source value, or NULL if the media item's source field is empty.
   *
   * @see \Drupal\media\MediaSourceInterface::getSourceFieldValue()
   *
   * @return bool|mixed|string
   *   Text to append to link text.
   *   If the link is not applicable, FALSE is returned.
   */
  public function getText($source);

}
