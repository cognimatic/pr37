<?php

namespace Drupal\media_link_enhancements\LinkGenerator;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGenerator;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\file\Entity\File;
use Drupal\media_link_enhancements\MediaLinkEnhancementsAppendTextInterface;
use Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface;

/**
 * Overrides the core link_generator service.
 *
 * This class override handles rewriting media links being rendered
 * as menu links.
 */
class MediaLinkEnhancementsLinkGenerator extends LinkGenerator implements LinkGeneratorInterface {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The module handler firing the route_link alter hook.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Append text service.
   *
   * @var \Drupal\media_link_enhancements\MediaLinkEnhancementsAppendTexInterface
   */
  protected $appendText;

  /**
   * Helper service.
   *
   * @var \Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface
   */
  protected $helper;

  /**
   * Constructs a new MediaLinkEnhancementsLinkGenerator object.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\media_link_enhancements\MediaLinkEnhancementsAppendTextInterface $append_text
   *   Append text service.
   * @param \Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface $helper
   *   Helper service.
   */
  public function __construct(UrlGeneratorInterface $url_generator, ModuleHandlerInterface $module_handler, RendererInterface $renderer, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $config_factory, MediaLinkEnhancementsAppendTextInterface $append_text, MediaLinkEnhancementsHelperInterface $helper) {
    $this->urlGenerator = $url_generator;
    $this->moduleHandler = $module_handler;
    $this->renderer = $renderer;
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $config_factory->get('media_link_enhancements.settings');
    $this->appendText = $append_text;
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public function generate($text, Url $url) {

    $typeSizeAppendingEnabled = !empty($this->configFactory->get('enable_type_size_appending'));
    $downloadAttrEnabled = !empty($this->configFactory->get('direct_linking_download_attr'));

    if (($typeSizeAppendingEnabled || $downloadAttrEnabled) && $url->isExternal() === FALSE && $url->isRouted() === TRUE) {
      $parameters = $url->getRouteParameters();

      // Check for links to media.
      if ($url->getRouteName() === 'entity.media.canonical' && $parameters && !empty($parameters['media'])) {

        $media = $this->entityTypeManager->getStorage('media')->load($parameters['media']);

        // Make sure there's a media object and the bundle is allowed.
        if ($media && $this->helper->checkBundle($media->bundle(), 'type_size_appending_bundles')) {

          $storage = $this->entityTypeManager->getStorage('file');
          $source = $storage->load($media->getSource()->getSourceFieldValue($media));

          if ($source) {
            // Pass the source off to the append_text service and check
            // for a non-FALSE response.
            $appendText = $this->appendText->getText($source);

            // Make sure the append text hasn't already been added
            // by media_link_enhancements_entity_display_build_alter.
            if ($typeSizeAppendingEnabled && $appendText && !is_array($text) && strpos($text, $appendText) === FALSE) {
              $text .= $appendText;
            }

            if ($source instanceof File && $downloadAttrEnabled) {
              $filename = $source->getFileName();
              $extension = pathinfo($filename, PATHINFO_EXTENSION);

              if ($this->helper->checkExtension($extension, 'direct_linking_download_attr_extensions')) {
                $url->mergeOptions(['attributes' => ['download' => $filename]]);
              }
            }
          }
        }
      }
    }

    return parent::generate($text, $url);
  }

}
