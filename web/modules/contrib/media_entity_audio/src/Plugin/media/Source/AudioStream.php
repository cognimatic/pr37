<?php

namespace Drupal\media_entity_audio\Plugin\media\Source;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\media\MediaSourceBase;
use Drupal\media\MediaTypeInterface;
use Drupal\media\MediaInterface;

/**
 * Provides media type plugin for Audio.
 *
 * @MediaSource(
 *   id = "audio_stream",
 *   label = @Translation("Audio Stream"),
 *   description = @Translation("Provides business logic and metadata for Audio Files."),
 *   allowed_field_types = {"link"},
 *   default_thumbnail_filename = "audio.png",
 * )
 */
class AudioStream extends MediaSourceBase {

  /**
   * {@inheritdoc}
   */
  public function getMetadata(MediaInterface $media, $attribute_name) {
    switch ($attribute_name) {
      case 'default_name':
        return basename($media->get($this->configuration['source_field'])->uri);
    }

    return parent::getMetadata($media, $attribute_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes() {
    return [
      'default_name' => $this->t('Name'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function createSourceField(MediaTypeInterface $type) {
    return parent::createSourceField($type);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareViewDisplay(MediaTypeInterface $type, EntityViewDisplayInterface $display) {
    $display->setComponent($this->getSourceFieldDefinition($type)->getName(), [
      'type' => 'audio_stream_html5',
      'label' => 'visually_hidden',
    ]);
  }

}
