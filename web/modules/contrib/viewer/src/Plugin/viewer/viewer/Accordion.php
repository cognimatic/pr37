<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "accordion",
 *   name = @Translation("Accordion"),
 *   provider = "viewer",
 *   empty_viewer_source = true,
 *   viewer_types = {}
 * )
 */
class Accordion extends Tabs {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $configuration = $this->getConfiguration();
    $items = [];
    $default_set = FALSE;
    $accordions = !empty($configuration['items']) ? $configuration['items'] : [];
    foreach ($accordions as $details) {
      if ($viewer = $this->getViewerByUuid($details['viewer_id'])) {
        $plugin = $viewer->getViewerPlugin()->setViewer($viewer);
        $items[] = [
          'title' => !empty($details['title']) ? $details['title'] : $viewer->label(),
          'content' => $plugin->getRenderable(),
          'is_default' => !empty($details['default']),
        ];
        if (!empty($details['default'])) {
          $default_set = TRUE;
        }
      }
    }
    if (!$default_set) {
      $items[0]['is_default'] = TRUE;
    }
    return [
      '#theme' => 'viewer_accordion',
      '#uuid' => $this->getId(),
      '#viewer' => $this->getViewer(),
      '#settings' => $this->getSettings(),
      '#accordion' => $items,
      '#attached' => [
        'library' => ['viewer/viewer.accordion'],
      ],
    ];
  }

}
