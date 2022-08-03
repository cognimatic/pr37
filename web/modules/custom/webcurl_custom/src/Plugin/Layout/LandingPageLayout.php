<?php

namespace Drupal\webcurl_custom\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\wcl_layout_builder\Plugin\Layout\WCLBaseLayout;

class LandingPageLayout extends WCLBaseLayout implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Base configuration for section.
    $configuration = $this->getConfiguration();
    // Build parent form.
    $form = parent::buildConfigurationForm($form, $form_state);
    // Specific column width options.
    $form['column_widths']['#options'] = $this->getColumnWidths();
    // Specific column class fields.
    $form['column_classes']['col1-1'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Top section"),
      '#default_value' => $configuration['column_classes']['col1-1'],
    ];
    $form['column_classes']['col1-2'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Sidebar section"),
      '#default_value' => $configuration['column_classes']['col1-2'],
    ];
    $form['column_classes']['col2-1'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Lower column 1"),
      '#default_value' => $configuration['column_classes']['col2-1'],
    ];
    $form['column_classes']['col2-2'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Lower column 2"),
      '#default_value' => $configuration['column_classes']['col2-2'],
    ];
    return $form;
  }

  /**
   * Provide Column Width Options.
   *
   * @return array
   *   Option array.
   */
  public function getColumnWidths() {
    return [
      'landing-page-layout' => 'Landing page layout',
    ];
  }

}