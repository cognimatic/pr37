<?php

declare(strict_types=1);

namespace Drupal\Tests\auditfiles\Functional;

use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\user\UserInterface;

/**
 * Tests that the "Not on server" report is reachable with no errors.
 *
 * @group auditfiles
 * @group auditfilesfunctional
 */
final class AuditFilesNotOnServerTest extends BrowserTestBase {

  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'file', 'user', 'auditfiles'];

  /**
   * User with admin privileges.
   */
  protected readonly UserInterface $user;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create user with permissions to manage site configuration and access
    // audit files reports.
    $this->user = $this->drupalCreateUser(['access audit files reports']);

    // Create File Entities.
    for ($i = 0; $i < 3; $i++) {
      $path = "public://example_$i.png";
      $image = File::create([
        'uri' => $path,
        'status' => TRUE,
      ]);
      $image->save();
    }
  }

  /**
   * Tests report page returns correct HTTP response code.
   *
   * 403 for anonymous users (also for users without permission).
   * 200 for authenticated user with 'access audit files reports' perm.
   */
  public function testReportPage(): void {
    // Form to test.
    $path = Url::fromRoute('auditfiles.reports.notonserver');
    // Establish session.
    $session = $this->assertSession();
    // Visit page as anonymous user, should receive a 403.
    $this->drupalGet($path);
    $session->pageTextContains('Access denied');
    $session->statusCodeEquals(403);
    // Log in as admin user.
    $this->drupalLogin($this->user);
    // Test that report page returns a 200 response code.
    $this->drupalGet($path);
    $session->pageTextContains('Not on server');
    $session->statusCodeEquals(200);
  }

  /**
   * Tests that an orphan file can be deleted.
   *
   * An "orphan" file is one in the file system that has no corresponding record
   * in the database.
   */
  public function testFileEntityCanBeDeleted(): void {
    // Form to test.
    $path = Url::fromRoute('auditfiles.reports.notonserver');
    // Establish session.
    $session = $this->assertSession();
    // Log in as admin user.
    $this->drupalLogin($this->user);
    // Load the report page.
    $this->drupalGet($path);
    // Check for the report title.
    $session->pageTextContains("Not on server");
    $session->pageTextContains('Found at least 3 file entities with missing files on disk.');
    $session->elementExists('css', '#audit-files-not-on-server');
    $session->elementExists('css', '#edit-files-1');
    // Check box for file ID to delete from database, and delete.
    $edit = [
      'edit-files-1' => TRUE,
    ];
    $this->submitForm($edit, 'Delete selected items from the database');
    // Check for correct confirmation page and submit.
    $session->pageTextContains("Delete these items from the database?");
    $session->pageTextContains("example_0.png and all usages will be deleted from the database.");
    $edit = [];
    $this->submitForm($edit, 'Confirm');
    // Check that target file is no longer listed.
    $session->pageTextContains("Not on server");
    $session->pageTextContains("Successfully deleted File ID : 1 from the file_managed table.");
    $session->pageTextContains("Found at least 2 file entities with missing files on disk.");
    $session->elementNotExists('css', '#edit-files-1');
  }

}
