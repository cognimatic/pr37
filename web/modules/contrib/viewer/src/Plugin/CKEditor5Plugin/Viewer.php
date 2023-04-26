<?php

declare(strict_types = 1);

namespace Drupal\viewer\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\Core\Url;
use Drupal\editor\EditorInterface;

/**
 * Plugin class to add dialog url for Viewer.
 */
class Viewer extends CKEditor5PluginDefault {

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $static_plugin_config['Viewer']['dialogURL'] = Url::fromRoute('viewer.ckeditor5_dialog')
      ->toString(TRUE)->getGeneratedUrl();
    $static_plugin_config['Viewer']['previewURL'] = Url::fromRoute('viewer.ckeditor5_preview',
      ['editor' => $editor->id()])
      ->toString(TRUE)->getGeneratedUrl();
    return $static_plugin_config;
  }

}
