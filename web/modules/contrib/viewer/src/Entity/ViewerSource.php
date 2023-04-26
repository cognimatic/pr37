<?php

namespace Drupal\viewer\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\user\EntityOwnerTrait;
use Drupal\viewer\Traits\CommonEntityTrait;
use Drupal\viewer\Traits\ViewerSourceTrait;

/**
 * Defines the ViewerSource entity.
 *
 * @ingroup viewer
 *
 * @ContentEntityType(
 *   id = "viewer_source",
 *   label = @Translation("Viewer Source"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\viewer\Entity\ListBuilder\ViewerSourceListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\viewer\Entity\AccessControl\ViewerSourceAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\viewer\Routing\ViewerSourceHtmlRouteProvider",
 *     },
 *     "form" = {
 *       "delete" = "Drupal\viewer\Entity\Form\ViewerSourceDeleteForm",
 *       "edit" = "Drupal\viewer\Entity\Form\ViewerSourceEditForm",
 *       "schedule" = "Drupal\viewer\Entity\Form\ViewerSourceScheduleForm",
 *       "notifications" = "Drupal\viewer\Entity\Form\ViewerSourceNotificationsForm",
 *     },
 *   },
 *   base_table = "viewer_source",
 *   data_table = "viewer_source_data",
 *   admin_permission = "administer viewer source",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "owner" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/viewer-source/{viewer_source}",
 *     "collection" = "/admin/structure/viewer-source",
 *     "delete-form" = "/admin/structure/viewer-source/{viewer_source}/delete",
 *     "edit-form" = "/admin/structure/viewer-source/{viewer_source}/edit",
 *     "new" = "/admin/structure/viewer-source/new",
 *     "bulk_import" = "/admin/structure/viewer-source/bulk-import",
 *     "settings" = "/admin/structure/viewer-source/{viewer_source}/settings",
 *     "schedule" = "/admin/structure/viewer-source/{viewer_source}/schedule",
 *     "notifications" = "/admin/structure/viewer-source/{viewer_source}/notifications",
 *     "enable" = "/admin/structure/viewer-source/{viewer_source}/enable",
 *     "disable" = "/admin/structure/viewer-source/{viewer_source}/disable",
 *     "download" = "/admin/structure/viewer-source/{viewer_source}/download",
 *     "import" = "/admin/structure/viewer-source/{viewer_source}/import",
 *   }
 * )
 */
class ViewerSource extends ContentEntityBase implements ViewerSourceInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use CommonEntityTrait;
  use ViewerSourceTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Owner'))
      ->setDescription(t('The user ID of the creator'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('This name will be displayed on the listing page and also will be used in reference fields'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['type_plugin'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE)
      ->setRequired(TRUE);

    $fields['source_plugin'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Source'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE)
      ->setRequired(TRUE);

    $fields['import_frequency'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Frequency'))
      ->setDescription(t('How often perform automatic imports (this process adds items to the Drupal Queue API). This configuration option also depends on how Cron is configured in the system.'))
      ->setSetting('allowed_values_function', 'viewer_import_frequencies')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'options_select',
      ])
      ->setDisplayOptions('form', [
        'label' => 'above',
        'type' => 'list_default',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDefaultValue(0);

    $fields['settings'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Settings'))
      ->setDescription(t('Stores viewer source settings'))
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['metadata'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Metadata'))
      ->setDescription(t('Stores file metadata'))
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['file_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('File'))
      ->setDescription(t('File ID of the permanently stored file'))
      ->setSetting('target_type', 'file')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('This option controls visiblity of the viewer source'))
      ->setSetting('on_label', t('Active'))
      ->setSetting('off_label', t('Inactive'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDefaultValue(TRUE);

    $fields['last_import'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Last Import'))
      ->setDescription(t('Timestamp of the last automated import'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
      ])
      ->setDefaultValue(NULL)
      ->setDisplayConfigurable('view', TRUE);

    $fields['next_import'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Next Import'))
      ->setDescription(t('Timestamp of the next automated import'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
      ])
      ->setDisplayOptions('form', [
        'label' => 'above',
        'type' => 'datetime',
      ])
      ->setDefaultValue(NULL)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);
    $values += ['user_id' => \Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    Cache::invalidateTags(['viewer_source:' . $this->id()]);
    \Drupal::cache('data')->delete('viewer_source:' . $this->id());
    parent::delete();
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    parent::save();
    if (!$this->isNew()) {
      Cache::invalidateTags(['viewer_source:' . $this->id()]);
    }
  }

}
