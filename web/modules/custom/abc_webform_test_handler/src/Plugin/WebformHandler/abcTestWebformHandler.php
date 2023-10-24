<?php

namespace Drupal\abc_webform_test_handler\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Form submission handler.
 *
 * Redirects to the [...] after the submit.
 *
 * @WebformHandler(
 *   id = "abc_webform_test_handler",
 *   label = @Translation("ABC Test preSave and submitForm functions"),
 *   category = @Translation("Webform Handler"),
 *   description = @Translation("This handler will manipulate the value of a <em>name</em> field - for testing purposes."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class abcTestWebformHandler extends WebformHandlerBase {

  public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {

    $values = $webform_submission->getData();
    ksm("Webform confirm form method");

    if (!empty($values['some_data'])) {
      $form_state->setRedirect('some_route.view', [
        'some_id' => $values['some_data'],
      ]);
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {


    //$webform_submission->preSave();
    //$webform_submission->save();
  }

  public function preSave(WebformSubmissionInterface $webform_submission) {
    $data = $webform_submission->getData();
    $sid = $data->id();

    if (!empty($data["name"])) {
      $data["name"] = $sid;
    }
    else {
      $data["name"] = $sid;
    }
    $webform_submission->setData($data);
  }
}
