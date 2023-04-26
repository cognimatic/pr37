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
use Drupal\viewer\Traits\ViewerTrait;

/**
 * Defines the Viewer entity.
 *
 * @ingroup viewer
 *
 * @ContentEntityType(
 *   id = "viewer",
 *   label = @Translation("Viewer"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\viewer\Entity\ListBuilder\ViewerListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\viewer\Entity\AccessControl\ViewerAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\viewer\Routing\ViewerHtmlRouteProvider",
 *     },
 *     "form" = {
 *       "delete" = "Drupal\viewer\Entity\Form\ViewerDeleteForm",
 *       "edit" = "Drupal\viewer\Entity\Form\ViewerEditForm",
 *       "settings" = "Drupal\viewer\Entity\Form\ViewerSettingsForm",
 *       "configuration" = "Drupal\viewer\Entity\Form\ViewerConfigurationForm",
 *       "filters" = "Drupal\viewer\Entity\Form\ViewerFiltersForm",
 *       "endpoint" = "Drupal\viewer\Entity\Form\ViewerEndpointForm",
 *       "iframe_preview" = "Drupal\viewer\Entity\Form\ViewerPreviewForm",
 *     },
 *   },
 *   base_table = "viewer",
 *   data_table = "viewer_data",
 *   admin_permission = "administer viewer",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "owner" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "collection" = "/admin/structure/viewers",
 *     "canonical" = "/admin/structure/viewers/{viewer}",
 *     "delete-form" = "/admin/structure/viewers/{viewer}/delete",
 *     "edit-form" = "/admin/structure/viewers/{viewer}/edit",
 *     "new" = "/admin/structure/viewers/new",
 *     "configuration" = "/admin/structure/viewers/{viewer}/configuration",
 *     "settings" = "/admin/structure/viewers/{viewer}/settings",
 *     "filters" = "/admin/structure/viewers/{viewer}/filters",
 *     "endpoint" = "/admin/structure/viewers/{viewer}/endpoint",
 *     "iframe_preview_src" = "/admin/structure/viewers/{viewer}/iframe-src",
 *     "iframe_preview" = "/admin/structure/viewers/{viewer}/iframe",
 *     "enable" = "/admin/structure/viewers/{viewer}/enable",
 *     "disable" = "/admin/structure/viewers/{viewer}/disable",
 *   }
 * )
 */
class Viewer extends ContentEntityBase implements ViewerInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use CommonEntityTrait;
  use ViewerTrait;

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

    $fields['viewer_plugin'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Viewer'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE)
      ->setRequired(TRUE);

    $fields['viewer_source'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Viewer Source'))
      ->setDescription(t('Viewer Source for this viewer'))
      ->setSetting('target_type', 'viewer_source')
      ->setSetting('handler', 'default:viewer_source')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ])
      ->setDisplayOptions('form', [
        'label' => 'above',
        'type' => 'options_select',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['filters'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Filters'))
      ->setDescription(t('Viewer Filters'))
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['settings'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Settings'))
      ->setDescription(t('Viewer Settings'))
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['configuration'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Configuration'))
      ->setDescription(t('Viewer Configuration'))
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('This option controls visiblity of the viewer'))
      ->setSetting('on_label', t('Active'))
      ->setSetting('off_label', t('Inactive'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDefaultValue(TRUE);

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
    Cache::invalidateTags(['viewer:' . $this->id()]);
    parent::delete();
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    if (!$this->isNew()) {
      Cache::invalidateTags(['viewer:' . $this->id()]);
    }
    parent::save();
  }

}
