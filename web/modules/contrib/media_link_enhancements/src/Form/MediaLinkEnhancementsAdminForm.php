<?php

namespace Drupal\media_link_enhancements\Form;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Media Link Enhancements settings form.
 */
class MediaLinkEnhancementsAdminForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'media_link_enhancements.settings';

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The field type plugin manager.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypePluginManager;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_plugin_manager
   *   The field type plugin manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $bundle_info, MessengerInterface $messenger, FieldTypePluginManagerInterface $field_type_plugin_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $bundle_info;
    $this->messenger = $messenger;
    $this->fieldTypePluginManager = $field_type_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('messenger'),
      $container->get('plugin.manager.field.field_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'media_link_enhancements_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $mediaConfig = $this->config('media.settings');

    if (!$mediaConfig->get('standalone_url')) {
      $this->messenger->addWarning($this->t('In order for Media Link Enhancements to work properly, the media setting <em>Standalone media URL</em> must be enabled in <a href="@media_settings_url">Media settings</a>.', [
        '@media_settings_url' => Url::fromUri('internal:/admin/config/media/media-settings')->toString(),
      ]));
    }

    $bundles = $this->entityTypeBundleInfo->getBundleInfo('media');

    $directLinkingBundleOptions = [];
    foreach ($bundles as $id => $info) {
      $directLinkingBundleOptions[$id] = $info['label'];
    }

    // Fieldset for direct linking feature.
    $form['direct_linking'] = [
      '#type' => 'details',
      '#title' => $this->t('Direct linking'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];

    $form['direct_linking']['enable_direct_linking'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable direct linking'),
      '#description' => $this->t('Alters media links so that they point directly to sources, e.g. <em>/sites/default/files/file.pdf</em>&nbsp; instead of <em>/media/1234</em>.'),
      '#default_value' => $config->get('enable_direct_linking') ? $config->get('enable_direct_linking') : FALSE,
    ];

    $form['direct_linking']['direct_linking_bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Media types'),
      '#options' => $directLinkingBundleOptions,
      '#states' => [
        'visible' => [
          ':input[name="direct_linking[enable_direct_linking]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('direct_linking_bundles') ? $config->get('direct_linking_bundles') : [],
    ];

    $form['direct_linking']['direct_linking_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Limit direct linking to specific file extensions'),
      '#description' => $this->t('Only alter media URLs for files with these extensions, e.g. <em>pdf,doc,txt</em>. If empty, URLs will be altered for all files. Does not apply to non file-based media, i.e. all oembed:video links will be altered regardless of file extensions included here.'),
      '#states' => [
        'visible' => [
          ':input[name="direct_linking[enable_direct_linking]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('direct_linking_extensions') ? $config->get('direct_linking_extensions') : NULL,
    ];

    $form['direct_linking']['direct_linking_download_attr'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add download attribute to links'),
      '#description' => $this->t('Alters media links so that they include the download attribute, e.g. <em>download="file.pdf"</em>. Only applies to file-based media, links generated by core and links in parsed content fields. Note that some browsers remove this attribute depending on the Content-Disposition header.'),
      '#states' => [
        'visible' => [
          ':input[name="direct_linking[enable_direct_linking]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('direct_linking_download_attr') ? $config->get('direct_linking_download_attr') : FALSE,
    ];

    $form['direct_linking']['direct_linking_download_attr_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Limit adding download attribute to specific file extensions'),
      '#description' => $this->t('Only add the download attribute to files with these extensions, e.g. <em>pdf,doc,txt</em>. If empty, the download attribute will be added to all links for all files.'),
      '#states' => [
        'visible' => [
          ':input[name="direct_linking[enable_direct_linking]"]' => ['checked' => TRUE],
          ':input[name="direct_linking[direct_linking_download_attr]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('direct_linking_download_attr_extensions') ? $config->get('direct_linking_download_attr_extensions') : NULL,
    ];

    // Fieldset for type/size appending feature.
    $form['type_size_appending'] = [
      '#type' => 'details',
      '#title' => $this->t('Type/size appending'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];

    $form['type_size_appending']['enable_type_size_appending'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable type/size appending'),
      '#description' => $this->t('Alters media links so that the file type and size is appended to the link text, e.g. <em>My Document [PDF/12KB]</em>.'),
      '#default_value' => $config->get('enable_type_size_appending') ? $config->get('enable_type_size_appending') : FALSE,
    ];

    $typeSizeAppendingBundleOptions = [];
    $validPlugins = ['audio_file', 'file', 'image', 'video_file'];

    foreach ($bundles as $id => $info) {
      $type = $this->entityTypeManager->getStorage('media_type')->load($id);
      if (in_array($type->getSource()->getPluginId(), $validPlugins)) {
        $typeSizeAppendingBundleOptions[$id] = $info['label'];
      }
    }

    $form['type_size_appending']['type_size_appending_bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Media types'),
      '#description' => $this->t('Only file-based media types are available for selection.'),
      '#options' => $typeSizeAppendingBundleOptions,
      '#states' => [
        'visible' => [
          ':input[name="type_size_appending[enable_type_size_appending]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('type_size_appending_bundles') ? $config->get('type_size_appending_bundles') : [],
    ];

    $form['type_size_appending']['type_size_appending_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Appended text prefix'),
      '#description' => $this->t('Character(s) displayed before the file size and type, e.g. <em>[</em> or <em>(</em>'),
      '#states' => [
        'visible' => [
          ':input[name="type_size_appending[enable_type_size_appending]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('type_size_appending_prefix') ? $config->get('type_size_appending_prefix') : NULL,
      '#size' => 20,
      '#maxlength' => 128,
    ];

    $form['type_size_appending']['type_size_appending_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Appended text separator'),
      '#description' => $this->t('Character(s) displayed in between the file size and type, e.g. <em>-</em> or <em>/</em>'),
      '#states' => [
        'visible' => [
          ':input[name="type_size_appending[enable_type_size_appending]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('type_size_appending_separator') ? $config->get('type_size_appending_separator') : NULL,
      '#size' => 20,
      '#maxlength' => 128,
    ];

    $form['type_size_appending']['type_size_appending_suffix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Appended text suffix'),
      '#description' => $this->t('Character(s) displayed after the file size and type, e.g. <em>]</em> or <em>)</em>'),
      '#states' => [
        'visible' => [
          ':input[name="type_size_appending[enable_type_size_appending]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('type_size_appending_suffix') ? $config->get('type_size_appending_suffix') : NULL,
      '#size' => 20,
      '#maxlength' => 128,
    ];

    $form['type_size_appending']['type_size_appending_uppercase'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display file type and size in uppercase'),
      '#description' => $this->t('Show the file extension and increment in uppercase, e.g. <em>[PDF/12KB]</em> instead of <em>[pdf/12kb]</em>.'),
      '#states' => [
        'visible' => [
          ':input[name="type_size_appending[enable_type_size_appending]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('type_size_appending_uppercase') ? $config->get('type_size_appending_uppercase') : FALSE,
    ];

    $form['type_size_appending']['type_size_appending_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Limit appending to specific file extensions'),
      '#description' => $this->t('A comma-separated list of extensions for which should include appended file type and size, e.g. <em>pdf,doc,txt</em>. If empty, all extensions will include appended text.'),
      '#states' => [
        'visible' => [
          ':input[name="type_size_appending[enable_type_size_appending]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('type_size_appending_extensions') ? $config->get('type_size_appending_extensions') : NULL,
    ];

    // Fieldset for redirect feature.
    $form['redirect'] = [
      '#type' => 'details',
      '#title' => $this->t('Redirection'),
      '#description' => $this->t('The redirection feature takes precedence over the binary response (below), i.e. if the same bundle is selected in both features, redirection will occur instead of a binary response.'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];

    $form['redirect']['enable_redirect'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable redirects'),
      '#description' => $this->t('Redirect stock media URLs, e.g. <em>/media/1234</em>, to the media sources.'),
      '#default_value' => $config->get('enable_redirect') ? $config->get('enable_redirect') : FALSE,
    ];

    $redirectBundleOptions = [];
    foreach ($bundles as $id => $info) {
      $redirectBundleOptions[$id] = $info['label'];
    }

    $form['redirect']['redirect_bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Media types'),
      '#options' => $redirectBundleOptions,
      '#states' => [
        'visible' => [
          ':input[name="redirect[enable_redirect]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('redirect_bundles') ? $config->get('redirect_bundles') : [],
    ];

    $form['redirect']['redirect_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Limit redirects to specific file extensions'),
      '#description' => $this->t('Only redirect to files with these extensions, e.g. <em>pdf,doc,txt</em>. If empty, all media will be redirected. Does not apply to non file-based media, i.e. all oembed:video links will be redirected regardless of file extensions included here.'),
      '#states' => [
        'visible' => [
          ':input[name="redirect[enable_redirect]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('redirect_extensions') ? $config->get('redirect_extensions') : NULL,
    ];

    // Fieldset for binary response feature.
    $form['binary_response'] = [
      '#type' => 'details',
      '#title' => $this->t('Binary response'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];

    $form['binary_response']['enable_binary_response'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable binary response'),
      '#description' => $this->t('Provides a direct, binary response. For example, returning an image directly at the <em>/media/1234</em> URL instead of the default, entity view display.'),
      '#default_value' => $config->get('enable_binary_response') ? $config->get('enable_binary_response') : FALSE,
    ];

    $binaryResponseBundleOptions = [];
    $validPlugins = ['audio_file', 'file', 'image', 'video_file'];

    foreach ($bundles as $id => $info) {
      $type = $this->entityTypeManager->getStorage('media_type')->load($id);
      if (in_array($type->getSource()->getPluginId(), $validPlugins)) {
        $binaryResponseBundleOptions[$id] = $info['label'];
      }
    }

    $form['binary_response']['binary_response_bundles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Media types'),
      '#description' => $this->t('Only file-based media types are available for selection.'),
      '#options' => $binaryResponseBundleOptions,
      '#states' => [
        'visible' => [
          ':input[name="binary_response[enable_binary_response]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('binary_response_bundles') ? $config->get('binary_response_bundles') : [],
    ];

    $form['binary_response']['binary_response_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Limit binary response to specific file extensions'),
      '#description' => $this->t('A comma-separated list of extensions for which a binary response is returned, e.g. <em>pdf,doc,txt</em>. If empty, a binary response will be returned for any extension.'),
      '#states' => [
        'visible' => [
          ':input[name="binary_response[enable_binary_response]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('binary_response_extensions') ? $config->get('binary_response_extensions') : NULL,
    ];

    // Fieldset for content parsing feature.
    $form['content_parsing'] = [
      '#type' => 'details',
      '#title' => $this->t('Content parsing'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];

    $form['content_parsing']['enable_content_parsing'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Parse links in content fields'),
      '#description' => $this->t('Alters media links in content fields of the selected field types, e.g. WYSIWYG body fields. Link anchor text may contain UTF-8 characters and markup, but HTML entities, e.g. <em>&amp;nbsp;</em>, in anchor text is not supported and link replacement will not occur.'),
      '#default_value' => $config->get('enable_content_parsing') ? $config->get('enable_content_parsing') : FALSE,
    ];

    // Gather valid field types.
    $field_type_options = [];
    foreach ($this->fieldTypePluginManager->getGroupedDefinitions($this->fieldTypePluginManager->getUiDefinitions()) as $category => $field_types) {
      foreach ($field_types as $name => $field_type) {
        if ($category !== 'Text') {
          continue;
        }
        $field_type_options[$name] = $field_type['label']->__toString();
      }
    }

    $form['content_parsing']['content_parsing_field_types'] = [
      '#type' => 'checkboxes',
      '#options' => $field_type_options,
      '#title' => $this->t('Field types for content parsing'),
      '#description' => $this->t('Fields types that should be parsed for media links.'),
      '#states' => [
        'visible' => [
          ':input[name="content_parsing[enable_content_parsing]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $config->get('content_parsing_field_types') ? $config->get('content_parsing_field_types') : [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    // A full array of all the settings in their groups.
    $settings = [
      'direct_linking' => [
        'enable_direct_linking',
        'direct_linking_bundles',
        'direct_linking_extensions',
        'direct_linking_download_attr',
        'direct_linking_download_attr_extensions',
      ],
      'type_size_appending' => [
        'enable_type_size_appending',
        'type_size_appending_bundles',
        'type_size_appending_prefix',
        'type_size_appending_separator',
        'type_size_appending_suffix',
        'type_size_appending_uppercase',
        'type_size_appending_extensions',
      ],
      'redirect' => [
        'enable_redirect',
        'redirect_bundles',
        'redirect_extensions',
      ],
      'binary_response' => [
        'enable_binary_response',
        'binary_response_bundles',
        'binary_response_extensions',
      ],
      'content_parsing' => [
        'enable_content_parsing',
        'content_parsing_field_types',
      ],
    ];

    foreach ($settings as $group_key => $group) {
      foreach ($group as $setting) {
        $config->set($setting, $form_state->getValue([$group_key, $setting]));
      }
    }

    // Finally save the configuration.
    $config->save();

    parent::submitForm($form, $form_state);
    $this->messenger->addWarning($this->t('You may need to clear Drupal cache for certain setting changes to take effect.'));
  }

}
