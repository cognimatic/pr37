<?php

namespace Drupal\viewer\Entity\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * ViewerSourceNotificationsForm controller for source notifications.
 *
 * @ingroup viewer
 */
class ViewerSourceNotificationsForm extends BaseContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    // Hide some fields from UI.
    unset($form['actions']['delete'], $form['import_frequency'], $form['name'], $form['next_import']);
    $settings = $this->entity->getSettings();

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Notification Events'),
      '#options' => [
        'success' => $this->t('Successful Imports'),
        'failed' => $this->t('Failed Imports'),
        'both' => $this->t('Both'),
      ],
      '#default_value' => !empty($settings['notifications']['type']) ? $settings['notifications']['type'] : 'both',
    ];

    $form['slack'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Slack Notifications'),
      '#description' => $this->t('You would need to create Slack app in order to obtain valid Hook URL.'),
      '#default_value' => !empty($settings['notifications']['slack']),
    ];

    $form['slack_hook_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Slack Hook URL'),
      '#description' => $this->t('<a href="https://slack.com/help/articles/115005265063-Incoming-webhooks-for-Slack" target="_blank">Incoming webhooks for Slack</a>'),
      '#states' => [
        'visible' => [':input[name="slack"]' => ['checked' => TRUE]],
      ],
      '#default_value' => !empty($settings['notifications']['slack_hook_url']) ? $settings['notifications']['slack_hook_url'] : '',
    ];

    $form['email'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Email Notifications'),
      '#default_value' => !empty($settings['notifications']['email']),
    ];

    $form['email_addresses'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email Addresses'),
      '#description' => $this->t('Email addresses separated by comma. Please check your Junk folder for emails, to solve this you may try install SMTP module and use a service like MailGun.'),
      '#states' => [
        'visible' => [':input[name="email"]' => ['checked' => TRUE]],
      ],
      '#default_value' => !empty($settings['notifications']['email_addresses']) ? $settings['notifications']['email_addresses'] : '',
    ];

    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.viewer_source.collection'),
      '#title' => $this->t('Cancel'),
      '#attributes' => [
        'class' => ['button', 'dialog-cancel'],
      ],
      '#weight' => 5,
    ];
    $form['actions']['#weight'] = 999;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $settings = [
      'notifications' => [
        'type' => $form_state->getValue('type'),
        'slack' => $form_state->getValue('slack'),
        'slack_hook_url' => $form_state->getValue('slack_hook_url'),
        'email' => $form_state->getValue('email'),
        'email_addresses' => $form_state->getValue('email_addresses'),
      ],
    ];
    $this->entity->mergeIntoSettings($settings);
    parent::save($form, $form_state);
    $message = $this->t('Viewer Source %label notifications updated', [
      '%label' => $entity->label(),
    ]);
    $this->loggerFactory->get('viewer')->notice($message);
    $this->messenger->addMessage($message);
    $form_state->setRedirect('entity.viewer_source.collection');
  }

}
