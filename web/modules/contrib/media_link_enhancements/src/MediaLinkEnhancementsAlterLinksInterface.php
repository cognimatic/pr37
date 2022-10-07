<?php

namespace Drupal\media_link_enhancements;

/**
 * Interface for the class that alters media links in content.
 */
interface MediaLinkEnhancementsAlterLinksInterface {

  /**
   * Alters media links in markup.
   *
   * @param string $content
   *   The content containing the links to alter.
   *
   * @return string
   *   The altered content.
   */
  public function alterLinks($content);

}
