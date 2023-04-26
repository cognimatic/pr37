<?php

namespace Drupal\viewer\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface ViewerSourceInterface plugins.
 */
interface ViewerSourceInterface extends PluginInspectionInterface {

  /**
   * Return the name of the ViewerSourceInterface plugin.
   */
  public function getName();

  /**
   * Build source upload form.
   */
  public function sourceForm(array $form, FormStateInterface $form_state, $viewer_type, $viewer_source);

  /**
   * Process uploaded file.
   */
  public function submitSourceForm(array &$form, FormStateInterface $form_state, $viewer_type);

  /**
   * Build import data form.
   */
  public function importForm(array $form, FormStateInterface $form_state, $viewer_type, $viewer_source);

  /**
   * Process uploaded file (import data).
   */
  public function submitImportForm(array &$form, FormStateInterface $form_state);

}
