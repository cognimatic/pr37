<?php

namespace Drupal\paragraphs_table\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;

/**
 * Plugin implementation of the 'paragraphs_table_widget' widget.
 *
 * @FieldWidget(
 *   id = "paragraphs_table_widget",
 *   label = @Translation("Paragraphs table"),
 *   description = @Translation("Paragraphs table form widget."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class ParagraphsTableWidget extends ParagraphsWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'vertical' => FALSE,
      'paste_clipboard' => FALSE,
      'show_all' => FALSE,
      'duplicate' => FALSE,
      'features' => ['duplicate' => 'duplicate'],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['vertical'] = [
      '#type' => 'checkbox',
      '#title' => t('Table vertical'),
      '#description' => t('If checked, table data will show in vertical mode.'),
      '#default_value' => !empty($this->getSetting('vertical')) ? $this->getSetting('vertical') : FALSE,
    ];
    $elements['paste_clipboard'] = [
      '#type' => 'checkbox',
      '#title' => t('Paste from clipboard'),
      '#description' => t('Add multiple rows, you can paste data from Excel'),
      '#default_value' => !empty($this->getSetting('paste_clipboard')) ? $this->getSetting('paste_clipboard') : FALSE,
    ];
    $cardinality = $this->fieldDefinition->get('fieldStorage')
      ->get('cardinality');
    if ($cardinality > 1) {
      $elements['show_all'] = [
        '#type' => 'checkbox',
        '#title' => t('Show all %cardinality items in form', ['%cardinality' => $cardinality]),
        '#description' => t('If checked, remove button add more.'),
        '#default_value' => !empty($this->getSetting('show_all')) ? $this->getSetting('show_all') : FALSE,
      ];
    }
    if (!in_array($cardinality, range(0, 3))) {
      $elements['features'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Enable widget features'),
        '#options' => [
          'duplicate' => $this->t('Duplicate'),
          // Reserve for future features.
        ],
        '#default_value' => $this->getSetting('features'),
        '#multiple' => TRUE,
      ];
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if (!empty($this->getSetting('vertical'))) {
      $summary[] = t('Table mode vertical');
    }
    if (!empty($this->getSetting('paste_clipboard'))) {
      $summary[] = t('Paste from Excel');
    }
    if (!empty($this->getSetting('show_all'))) {
      $cardinality = $this->fieldDefinition->get('fieldStorage')
        ->get('cardinality');
      $summary[] = t('Show all %cardinality elements in form', ['%cardinality' => $cardinality]);
    }
    $features = array_filter($this->getSetting('features'));
    if (!empty($features)) {
      $summary[] = $this->t('Features: @features', ['@features' => implode(', ', $features)]);
    }

    return $summary;
  }

  /**
   * For multiple elements.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);
    $settings = $this->fieldDefinition->getSettings();
    $handler = $settings['handler_settings'];
    if (!empty($handler["target_bundles"]) && count($handler["target_bundles"]) > 1) {
      return $elements;
    }
    $target_type = $this->getFieldSetting('target_type');
    $default_type = $this->getDefaultParagraphTypeMachineName();
    $field_name = $this->fieldDefinition->getName();
    $widgetState = static::getWidgetState($this->fieldParents, $field_name, $form_state);
    $widgetState["paragraphs"][0]["mode"] = 'edit';
    $elements["#paragraphsTable"]["#widget_state"] = $widgetState;
    $elements["#paragraphsTable"]["#table_vertical"] = $this->getSetting('vertical');
    $elements["#paragraphsTable"]["#paste_clipboard"] = $this->getSetting('paste_clipboard');
    $elements["#paragraphsTable"]["#show_all"] = $this->getSetting('show_all');
    $elements["#paragraphsTable"]["#feature"] = $this->getSetting('features');
    if (empty($default_type)) {
      $default_type = array_key_first($this->getAllowedTypes());
    }
    if (empty($default_type)) {
      return $elements;
    }
    $formDisplay = \Drupal::service('entity_display.repository')
      ->getFormDisplay($target_type, $default_type);
    $components = $formDisplay->getComponents();
    uasort($components, 'Drupal\Component\Utility\SortArray::sortByWeightElement');

    /** @var \Drupal\paragraphs\ParagraphInterface $paragraphs_entity */
    $paragraphs_entity = \Drupal::entityTypeManager()->getStorage($target_type)
      ->create(['type' => $default_type]);
    $field_definitions = $paragraphs_entity->getFieldDefinitions();

    foreach ($components as $name => $setting) {
      if (!empty($field_definitions[$name]) && $field_definitions[$name] instanceof FieldConfigInterface) {
        $elements["#paragraphsTable"]['#fields'][$name] = $field_definitions[$name];
      }
    }
    // Remove fieldgroups we don't need it.
    if (!empty($elements[0]) && $elements[0]['subform']) {
      foreach (range(0, $elements["#max_delta"]) as $delta) {
        $elements[$delta]["subform"]["#fieldgroups"] = [];
      }

    }
    return $elements;
  }

}
