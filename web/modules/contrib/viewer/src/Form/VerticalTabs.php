<?php

namespace Drupal\viewer\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\viewer\Form\Viewer\BaseForm;

/**
 * VerticalTabs for the vertcal tabs viewer plugin.
 *
 * @ingroup viewer
 */
class VerticalTabs extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'viewer_vertical_tabs_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $vertical_tabs = [], $settings = []) {
    $form['#attached']['library'][] = 'viewer/viewer.vertical_tabs';
    if (!empty($settings['title'])) {
      $form['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $settings['title'],
      ];
    }
    if (!empty($settings['subtitle'])) {
      $form['subtitle'] = [
        '#type' => 'html_tag',
        '#tag' => 'h4',
        '#value' => $settings['subtitle'],
      ];
    }
    if (!empty($settings['header_summary'])) {
      $form['header_summary'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $settings['header_summary'],
      ];
    }
    $form['vertical_tabs'] = [
      '#type' => 'vertical_tabs',
    ];
    foreach ($vertical_tabs as $key => $details) {
      $form[$key] = [
        '#type' => 'details',
        '#title' => $details['title'],
        '#group' => 'vertical_tabs',
      ];
      $form[$key]['viewer'] = $details['element'];
      if (!empty($details['is_default'])) {
        $form['vertical_tabs']['#default_tab'] = 'edit-' . str_replace('_', '-', $key);
      }
    }
    if (!empty($settings['footer_summary'])) {
      $form['footer_summary'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $settings['footer_summary'],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

}
