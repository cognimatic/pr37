<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "vertical_tabs",
 *   name = @Translation("Tabs (vertical)"),
 *   provider = "viewer",
 *   empty_viewer_source = true,
 *   viewer_types = {}
 * )
 */
class VerticalTabs extends Tabs {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $configuration = $this->getConfiguration();
    $tabs = !empty($configuration['items']) ? $configuration['items'] : [];
    $vertical_tabs = [];
    $key_indexes = [];
    foreach ($tabs as $details) {
      if ($viewer = $this->getViewerByUuid($details['viewer_id'])) {
        $plugin = $viewer->getViewerPlugin()->setViewer($viewer);
        $title = !empty($details['title']) ? $details['title'] : $viewer->label();
        $key = $this->getMachineName($title);
        if (!isset($key_indexes[$key])) {
          $key_indexes[$key] = 0;
        }
        else {
          $key_indexes[$key]++;
        }
        $vertical_tabs[$key . '_' . $key_indexes[$key]] = [
          'title' => $title,
          'element' => $plugin->getRenderable(),
          'is_default' => !empty($details['default']),
        ];
      }
    }
    return \Drupal::formBuilder()->getForm('\Drupal\viewer\Form\VerticalTabs', $vertical_tabs, $this->getSettings());
  }

}
