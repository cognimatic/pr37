<?php

namespace Drupal\webcurl_custom\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;

class JobBanner extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
        'background_type' => '',
        'background_field' => '',
        'background_image' => '',
      'content_color' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $current_path = \Drupal::service('path.current')->getPath();
    $splitPath = explode('/', $current_path);
    $nid = NULL;
    $contentFields = [];
    foreach ($splitPath as $pathComp) {
      $lol = strpos($pathComp, 'node');
      if (strpos($pathComp, 'node') !== FALSE) {
        $split = explode('.', $pathComp);
        $nid = $split[1];
        break;
      }
    }
    if (!is_null($nid)) {
      $node = Node::load($nid);
      $fields = $node->getFields();
      foreach ($fields as $field => $fieldObj) {
        $fieldDef = $fieldObj->getFieldDefinition();
        if (method_exists($fieldDef, 'get')) {
          $settings = [];
          if (method_exists($fieldDef, 'getSettings')) {
            $settings = $fieldDef->getSettings();
          }
          if (
            $fieldDef->get('field_type') === 'image' ||
            (
              isset($settings['handler']) &&
              $settings['handler'] === 'default:media'
            )
          ) {
            $contentFields[$field] = $fieldDef->label();
          }
        }
      }
    }
    // Base configuration for section.
    $configuration = $this->getConfiguration();
    // Global configuration for section backgrounds.
    $config = \Drupal::service('config.factory')->getEditable('wcl_layout_builder.settings');
    $backgroundColor = $config->get($configuration['background_color']);
    $backgroundSection1 = $config->get('layout_section_1');
    $backgroundSection2 = $config->get('layout_section_2');
    $backgroundSection3 = $config->get('layout_section_3');
    $backgroundSection4 = $config->get('layout_section_4');
    $backgroundSection5 = $config->get('layout_section_5');
    $mediaImage = NULL;
    if ($configuration['background_image']) {
      $mediaImage = Media::load($configuration['background_image']);
    }

    $textColor = '#000000';
    if (!is_array($backgroundColor)) {
      if (color_valid_hexadecimal_string($backgroundColor)) {
        $rgb = _color_unpack($backgroundColor);
        $hsl = _color_rgb2hsl($rgb);
        if ($hsl[2] < 127) {
          $textColor = '#ffffff';
        }
      }
    }
    else {
      $backgroundColor = 'transparent';
    }
    // Form elements.
    $form['background_type'] = [
      '#type' => 'select',
      '#title' => t("Background source"),
      '#default_value' => $configuration['background_type'],
      '#empty_option' => '- None -',
      '#options' => [
        'media' => 'Media',
        'content' => 'Content',
      ]
    ];
    $form['background_image'] = [
      '#type' => 'entity_autocomplete',
      '#title' => t("Section's background image"),
      '#target_type' => 'media',
      '#default_value' => $mediaImage,
      '#states' => [
        'visible' => [
          ':input[name="layout_settings[background_type]"]' => ['value' => 'media'],
        ]
      ],
    ];
    $form['background_field'] = [
      '#type' => 'select',
      '#title' => t("Background image source"),
      '#description' => t('Choose the field on this content type to provide the background image from.'),
      '#default_value' => $configuration['background_field'],
      '#options' => $contentFields,
      '#empty_option' => '- None -',
      '#states' => [
        'visible' => [
          ':input[name="layout_settings[background_type]"]' => ['value' => 'content'],
        ]
      ]
    ];
    $form['background_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Background overlay color"),
      '#default_value' => $configuration['background_color'],
      '#description' => $this->t('Apply a semi-transparent color overlay to the background image. Takes a #HEXHEX value.'),
    ];
    $form['content_color'] = [
      '#type' => 'select',
      '#title' => $this->t("Background color for the content section."),
      '#default_value' => $configuration['content_color'],
      '#options' => [
        '' => '- Select -',
        'layout_section_1' => "Background 1 ($backgroundSection1)",
        'layout_section_2' => "Background 2 ($backgroundSection2)",
        'layout_section_3' => "Background 3 ($backgroundSection3)",
        'layout_section_4' => "Background 4 ($backgroundSection4)",
        'layout_section_5' => "Background 5 ($backgroundSection5)",
      ],
      '#description' => $this->t('Choose a defined background color for the content section.'),
      '#attributes' => [
        'dir' => 'ltr',
        'style' => "background-color: $backgroundColor; color: $textColor",
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['background_type'] = $form_state->getValue('background_type');
    $this->configuration['background_image'] = $form_state->getValue('background_image');
    $this->configuration['background_field'] = $form_state->getValue('background_field');
    $this->configuration['background_color'] = $form_state->getValue('background_color');
    $this->configuration['content_color'] = $form_state->getValue('content_color');
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);

    // Starting Values.
    $backgroundColor = $this->configuration['background_color'];
    $backgroundType = $this->configuration['background_type'];
    $backgroundImage = $this->configuration['background_image'];
    $backgroundField = $this->configuration['background_field'];
    $contentColor = $this->configuration['content_color'];

    $backgroundImageUrl = '';

    // Load backgroundImage URL.
    switch ($backgroundType) {
      // Content (i.e. field)
      case 'content':
        if ($backgroundField) {
          // Find node.
          $node = \Drupal::routeMatch()->getParameter('node');
          if ($node) {
            $field = $node->get($backgroundField);
            $value = $field->getValue();
            // Find field def.
            $fieldDef = $field->getFieldDefinition();
            if (method_exists($fieldDef, 'get')) {
              $settings = [];
              if (method_exists($fieldDef, 'getSettings')) {
                $settings = $fieldDef->getSettings();
              }
              // If image field.
              if ($fieldDef->get('field_type') === 'image') {
                $target = isset($value[0]["target_id"]) ?
                  $value[0]["target_id"] :
                  NULL;

                if (!is_null($target)) {
                  $loadFile = File::load($target);
                  $loadURI = $loadFile->get('uri');
                  $loadValue = $loadURI->getValue();
                  $backgroundImageUrl = $loadValue[0]["value"];
                }
              }
              // If media field.
              elseif (isset($settings['handler']) && $settings['handler'] === 'default:media') {
                $target = isset($value[0]["target_id"]) ?
                  $value[0]["target_id"] :
                  NULL;

                if (!is_null($target)) {
                  $entityMediaImage = Media::load($target);
                  if ($entityMediaImage && $entityMediaImage->field_media_image->entity) {
                    $mediaFileUri = $entityMediaImage->field_media_image->entity->getFileUri();
                    $backgroundImageUrl = $mediaFileUri;
                  }
                }
              }
            }
          }
        }
        break;
      // Media (i.e. direct entity reference).
      case 'media':
        // Load background image and get URL (if set).
        if ($backgroundImage) {
          $entityMediaImage = Media::load($backgroundImage);
          if ($entityMediaImage) {
            $mediaFileUri = $entityMediaImage->field_media_image->entity->getFileUri();
            $backgroundImageUrl = $mediaFileUri;
          }
        }
        break;

    }

    // Base classes.
    $build['#attributes']['class'] = [
      'row',
      'layout',
      $this->getPluginDefinition()->getTemplate(),
    ];

    // Add background_color class if set.
    if ($contentColor) {
      $config = \Drupal::service('config.factory')->getEditable('wcl_layout_builder.settings');
      $contentSectionColor = $config->get($contentColor);
      $build['#settings']['content_color'] = $contentSectionColor;
    }

    // Add background_image if set.
    if ($backgroundImageUrl) {
      $build['#settings']['background_image'] = $backgroundImageUrl;
    }

    return $build;
  }

}