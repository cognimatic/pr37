<?php

namespace Drupal\Tests\localgov_paragraphs\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests that all config provided by this module passes validation.
 *
 * @group localgov_paragraphs
 */
class CheckConfigTest extends EntityKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'address',
    'entity_reference_revisions',
    'field_formatter_class',
    'field_group',
    'file',
    'fontawesome',
    'geolocation',
    'geolocation_google_maps',
    'layout_discovery',
    'layout_paragraphs',
    'link',
    'media',
    'media_library',
    'node',
    'office_hours',
    'options',
    'paragraphs',
    'paragraphs_library',
    'tablefield',
    'telephone',
    'views',
    'viewsreference',
    'localgov_paragraphs',
    'localgov_paragraphs_layout',
    'localgov_paragraphs_views',
    'localgov_subsites_paragraphs',
    'localgov_homepage_paragraphs',
  ];

  /**
   * Tests that the module's config installs properly.
   */
  public function testConfig() {

    $this->installSchema('file', ['file_usage']);
    $this->installEntitySchema('file');
    $this->installEntitySchema('paragraph');
    $this->installConfig([
      'localgov_paragraphs',
      'localgov_paragraphs_layout',
      'localgov_subsites_paragraphs',
      'localgov_paragraphs_views',
      'localgov_homepage_paragraphs',
    ]);
  }

}
