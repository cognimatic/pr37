<?php

namespace Drupal\media_link_enhancements;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\file\Entity\File;

/**
 * Service class for the alter links functions.
 *
 * Scans markup and updates media links.
 */
class MediaLinkEnhancementsAlterLinks implements MediaLinkEnhancementsAlterLinksInterface {

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Append text service.
   *
   * @var \Drupal\media_link_enhancements\MediaLinkEnhancementsAppendTextInterface
   */
  protected $appendText;

  /**
   * Helper service.
   *
   * @var \Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface
   */
  protected $helper;

  /**
   * Constructs a MediaLinkEnhancementsAlterLinks object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\media_link_enhancements\MediaLinkEnhancementsAppendTextInterface $append_text
   *   Append text service.
   * @param \Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface $helper
   *   Helper service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager, EntityRepositoryInterface $entity_repository, MediaLinkEnhancementsAppendTextInterface $append_text, MediaLinkEnhancementsHelperInterface $helper) {
    $this->configFactory = $config_factory->get('media_link_enhancements.settings');
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRepository = $entity_repository;
    $this->appendText = $append_text;
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public function alterLinks($content) {

    if (empty($content)) {
      return $content;
    }

    $directLinkingEnabled = !empty($this->configFactory->get('enable_direct_linking'));
    $typeSizeAppendingEnabled = !empty($this->configFactory->get('enable_type_size_appending'));

    // Bail if neither feature is enabled.
    if (!$directLinkingEnabled && !$typeSizeAppendingEnabled) {
      return $content;
    }

    $doc = new \DOMDocument();

    // Fixes Warning: DOMDocument::loadHTML(): Tag drupal-entity invalid in
    // Entity.
    $internalErrors = libxml_use_internal_errors(TRUE);

    $content_utf = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
    $doc->loadHTML($content_utf);
    libxml_use_internal_errors($internalErrors);

    $links = $doc->getElementsByTagName('a');

    foreach ($links as $link) {

      $type = $link->getAttribute('data-entity-type');

      // Bail out if we know it's not a media link.
      if (!empty($type) && $type !== 'media') {
        continue;
      }

      $uuid = $link->getAttribute('data-entity-uuid');

      $entity = FALSE;
      if (!empty($uuid) && !empty($type)) {
        $entity = $this->entityRepository->loadEntityByUuid($type, $uuid);
      }
      else {
        $href = $link->getAttribute('href');

        // Bail if there's no href.
        if (empty($href)) {
          continue;
        }

        // Bail out if this is not a media link.
        if (strpos($href, '/media/') !== 0) {
          continue;
        }

        // Bail if this is an external link.
        $components = parse_url($href);
        if (!empty($components['host'])) {
          continue;
        }

        // Get the ID.
        $id = str_replace('/media/', '', $href);
        $entity = $this->entityTypeManager->getStorage('media')->load($id);
      }

      // Bail if we don't have an entity or the entity isn't published..
      if (!$entity || !$entity->isPublished()) {
        continue;
      }

      $source = $entity->getSource()->getSourceFieldValue($entity);
      $sub = $link->getAttribute('data-entity-substitution');

      // Get the full link to replace before we modify it.
      $full_link = $link->C14N();

      $replaceLink = FALSE;
      $append = FALSE;
      $file = FALSE;
      $url = FALSE;

      // Is this a direct link to a video?
      if (strpos($source, 'http') !== FALSE) {
        $url = $source;
      }
      elseif (is_numeric($source)) {
        $file = $this->entityTypeManager->getStorage('file')->load($source);
        if ($file && $file instanceof File) {
          $url = str_replace('public://', '', $file->getFileUri());
          $url = '/' . PublicStream::basePath() . '/' . $url;
        }
      }

      if ($url && $directLinkingEnabled && $this->helper->checkBundle($entity->bundle(), 'direct_linking_bundles')) {
        $replaceUrl = TRUE;
        if ($file) {
          $extension = pathinfo($file->getFileName(), PATHINFO_EXTENSION);
          if (!$this->helper->checkExtension($extension, 'direct_linking_extensions')) {
            $replaceUrl = FALSE;
          }
        }

        if ($replaceUrl) {
          // Only replace the URL if this isn't a Linkit link.
          if (empty($sub)) {
            $link->setAttribute('href', $url);
            $replaceLink = TRUE;
          }

          if ($file && $this->configFactory->get('direct_linking_download_attr')) {
            $extension = pathinfo($file->getFileName(), PATHINFO_EXTENSION);
            if ($this->helper->checkExtension($extension, 'direct_linking_download_attr_extensions')) {
              $link->setAttribute('download', $file->getFileName());
              $replaceLink = TRUE;
            }
          }
        }

      }

      if ($typeSizeAppendingEnabled && $this->helper->checkBundle($entity->bundle(), 'type_size_appending_bundles')) {
        // The appendText service handles extension checking.
        if ($append = $this->appendText->getText($file)) {
          $replaceLink = TRUE;
        }
      }

      // Replace the original link if required.
      if ($replaceLink) {

        // Create the new link.
        $new = $doc->saveHTML($link);

        // Appending the file type/size if applicable.
        if ($append) {
          // Handle any HTML inside the anchor tag.
          $inner = '';
          foreach ($link->childNodes as $child) {
            $inner .= $child->ownerDocument->saveXML($child);
          }
          $new = str_replace($inner, ($inner . $append), $new);
        }

        // Perform the replacement.
        // NOTE: If the link text contains HTML entities, this won't match
        // because DOMDocument is decoding these. For now, I've not found a
        // solution that handles both UTF-8 encoded characters and HTML
        // entities at the same time.
        $content = str_replace($full_link, $new, $content);
      }
    }

    return $content;
  }

}
