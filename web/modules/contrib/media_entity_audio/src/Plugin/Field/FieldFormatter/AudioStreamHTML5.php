<?php

namespace Drupal\media_entity_audio\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'Audio Stream (HTML5)' formatter.
 *
 * @FieldFormatter(
 *   id = "audio_stream_html5",
 *   label = @Translation("Audio Stream (HTML5)"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class AudioStreamHTML5 extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings['controls'] = TRUE;
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['controls'] = [
      '#title' => $this->t('Display controls'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('controls'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($this->getSetting('controls')) {
      $summary[] = $this->t('Audio controls displayed.');
    }
    else {
      $summary[] = $this->t('Audio controls not displayed.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme' => 'media_audio',
        '#sources' => [$item->uri],
        '#controls' => $this->getSetting('controls'),
      ];

      // Pass field item attributes to the theme function.
      if (isset($item->_attributes)) {
        $elements[$delta] += ['#attributes' => []];
        $elements[$delta]['#attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }

    }
    return $elements;
  }

}
