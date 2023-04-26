<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\viewer\Plugin\ViewerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Xss;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "tabs",
 *   name = @Translation("Tabs"),
 *   provider = "viewer",
 *   empty_viewer_source = true,
 *   viewer_types = {}
 * )
 */
class Tabs extends ViewerBase {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $configuration = $this->getConfiguration();
    $items = [];
    $default_set = FALSE;
    $tabs = !empty($configuration['items']) ? $configuration['items'] : [];
    foreach ($tabs as $details) {
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
      '#theme' => 'viewer_tabs',
      '#uuid' => $this->getId(),
      '#viewer' => $this->getViewer(),
      '#settings' => $this->getSettings(),
      '#tabs' => $items,
      '#attached' => [
        'library' => ['viewer/viewer.tabs'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function configurationForm(array $form, FormStateInterface $form_state, $params = []) {
    $configuration = $params['configuration'];
    if (empty($form_state->get('tabs')) && !empty($configuration['items']) && empty($form_state->get('tabs_loaded'))) {
      $form_state->set('tabs_loaded', TRUE);
      $form_state->set('tabs', $configuration['items']);
    }
    $tabs = !empty($form_state->get('tabs')) ? $form_state->get('tabs') : [];

    $wrapper_id = 'viewer-tabs-wrapper';
    $group_class = 'group-order-weight';

    $form['tabs'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Viewer'), $this->t('Title'),
        $this->t('Default'), '', $this->t('Weight'),
      ],
      '#tabledrag' => [[
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => $group_class,
      ],
      ],
      '#empty' => $this->t('There are no tabs yet. Please add a tab below.'),
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
      '#attached' => [
        'library' => ['viewer/viewer.tabs_admin'],
      ],
    ];

    $weight = 0;
    foreach ($tabs as $index => $details) {
      $form['tabs'][$index]['#attributes']['class'][] = 'draggable';
      $form['tabs'][$index]['#weight'] = $weight;

      if ($this->getViewerByUuid($details['viewer_id'])) {
        $form['tabs'][$index]['viewer_id'] = [
          '#type' => 'select',
          '#title' => $this->t('Viewer'),
          '#title_display' => 'invisible',
          '#options' => $this->getViewers(),
          '#default_value' => !empty($details['viewer_id']) ? Xss::filter($details['viewer_id']) : '',
          '#empty_option' => $this->t('- Select Viewer -'),
        ];
      }
      else {
        $form['tabs'][$index]['label'] = [
          '#plain_text' => $this->t('Does not exists'),
        ];
      }

      $form['tabs'][$index]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#title_display' => 'invisible',
        '#default_value' => !empty($details['title']) ? Xss::filter($details['title']) : '',
        '#placeholder' => $this->t('Viewer label will be used if empty'),
      ];

      $form['tabs'][$index]['default'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Default'),
        '#title_display' => 'invisible',
        '#default_value' => !empty($details['default']),
      ];

      $form['tabs'][$index]['remove'] = [
        '#type' => 'submit',
        '#value' => $this->t('x'),
        '#submit' => [
          [$this, 'removeTab'],
        ],
        '#name' => $index,
        '#ajax' => [
          'callback' => [$this, 'ajaxCallback'],
          'wrapper' => $wrapper_id,
        ],
      ];

      $form['tabs'][$index]['weight'] = [
        '#type' => 'weight',
        '#title' => '',
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#attributes' => ['class' => [$group_class]],
      ];
      $weight++;
    }

    $form['new'] = [
      '#type' => 'table',
    ];

    $form['new'][0]['viewer_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Viewer'),
      '#title_display' => 'invisible',
      '#options' => $this->getViewers(),
      '#empty_option' => $this->t('- Select Viewer -'),
    ];

    $form['new'][0]['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#title_display' => 'invisible',
      '#placeholder' => $this->t('Tab title, viewer label will be used if empty'),
    ];

    $form['new'][0]['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add new item'),
      '#submit' => [
        [$this, 'addTab'],
      ],
      '#ajax' => [
        'callback' => [$this, 'ajaxCallback'],
        'wrapper' => $wrapper_id,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationValues(array &$form, FormStateInterface $form_state) {
    $tabs = [];
    foreach ($form_state->getValue('tabs') as $index => $details) {
      $tabs[$index] = [
        'viewer_id' => $details['viewer_id'],
        'title' => $details['title'],
        'weight' => $details['weight'],
        'default' => $details['default'],
      ];
    }
    return ['items' => $tabs];
  }

  /**
   * Callback for both ajax-enabled buttons.
   */
  public static function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['tabs'];
  }

  /**
   * Submit handler for the "add tab" button.
   */
  public static function addTab(array &$form, FormStateInterface $form_state) {
    $tabs = !empty($form_state->get('tabs')) ? $form_state->get('tabs') : [];
    $new = $form_state->getValue('new');
    $tabs[count($tabs) + 1] = [
      'viewer_id' => $new[0]['viewer_id'],
      'title' => $new[0]['title'],
    ];
    $form_state->set('tabs', $tabs)->setRebuild();
  }

  /**
   * Submit handler for the "remove tab" button.
   */
  public static function removeTab(array &$form, FormStateInterface $form_state) {
    $tabs = $form_state->get('tabs');
    $triggering_element = $form_state->getTriggeringElement();
    $header_index = $triggering_element['#name'];
    if (!empty($tabs[$header_index])) {
      unset($tabs[$header_index]);
    }
    $form_state->set('tabs', $tabs)->setRebuild();
  }

  /**
   * Get list of all available Viewers.
   */
  protected function getViewers() {
    $sources = [];
    $storage = \Drupal::entityTypeManager()->getStorage('viewer');
    $ids = $storage->getQuery()
      ->sort('created', 'DESC')
      ->accessCheck(TRUE)
      ->execute();
    $entities = $storage->loadMultiple($ids);
    foreach ($entities as $entity) {
      $configuration = $entity->getConfiguration();
      $status = $entity->isPublished() ? $this->t('Active') : $this->t('Inactive');
      // We can't reference to the same viewer plugin type.
      // We exclude current plugin type from the list.
      if ($entity->getViewerPlugin()->getPluginId() != $this->getPluginId()) {
        if (!empty($configuration['items'])) {
          // We need to make sure no nested viewer references.
          $matched = FALSE;
          foreach ($configuration['items'] as $items) {
            if (!empty($items['viewer_id']) && $items['viewer_id'] == $this->getId()) {
              $matched = TRUE;
              break;
            }
          }
          if (!$matched) {
            $sources[$entity->uuid()] = $entity->label() . ' (' . $status . ')';
          }
        }
        else {
          $sources[$entity->uuid()] = $entity->label() . ' (' . $status . ')';
        }
      }
    }
    return $sources;
  }

  /**
   * Load Viewer by uuid.
   */
  protected function getViewerByUuid($uuid) {
    $entities = \Drupal::entityTypeManager()->getStorage('viewer')->loadByProperties(['uuid' => $uuid]);
    return reset($entities);
  }

}
