<?php

namespace Drupal\Tests\media_entity_audio\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\media\Entity\Media;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Tests the audio stream media source.
 *
 * @group media
 */
class MediaSourceAudioStreamTest extends KernelTestBase {

  use MediaTypeCreationTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'media',
    'media_entity_audio',
    'user',
    'field',
    'system',
    'link',
    'file',
    'image',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('media');
    $this->installEntitySchema('file');
    $this->installEntitySchema('user');
    $this->installSchema('file', ['file_usage']);
    $this->installConfig(['system', 'image']);

  }

  /**
   * Tests the file extension constraint.
   */
  public function testFileExtensionConstraint() {
    $media_type = $this->createMediaType('audio_stream');

    // Add the source field to the form display for the media type.
    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $view_display */
    $view_display = \Drupal::service('entity_display.repository')->getViewDisplay('media', $media_type->id(), 'default');
    $media_type->getSource()->prepareViewDisplay($media_type, $view_display);
    $view_display->removeComponent('thumbnail');
    $view_display->save();

    $metadata_attributes = $media_type->getSource()->getMetadataAttributes();
    $this->assertEquals('Name', (string) $metadata_attributes['default_name']);

    $media = Media::create([
      'field_media_audio_stream' => 'http://bbcmedia.ic.llnwd.net/stream/bbcmedia_radio1_mf_p',
      'bundle' => $media_type->id(),
    ]);
    $media->save();

    $this->assertEquals('bbcmedia_radio1_mf_p', $media->label());

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('media');
    $build = $view_builder->view($media);

    $html = \Drupal::service('renderer')->renderPlain($build);
    $this->assertContains('<audio  controls="controls"><source src="http://bbcmedia.ic.llnwd.net/stream/bbcmedia_radio1_mf_p"></audio>', (string) $html);
  }

}
