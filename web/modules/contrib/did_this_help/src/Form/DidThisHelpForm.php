<?php

namespace Drupal\did_this_help\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\TitleResolver;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Session\AccountInterface;

/**
 * Class DidThisHelpForm.
 */
class DidThisHelpForm extends FormBase {

  /**
   * The title resolver service.
   *
   * @var \Drupal\Core\Controller\TitleResolver
   */
  protected $titleResolver;

  /**
   * @var AccountInterface $account
   */
  protected $account;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Controller\TitleResolver $title_resolver
   *   The title resolver service.
   * @param \Drupal\Core\Session\AccountInterface
   *   Current user.
   */
  public function __construct(TitleResolver $title_resolver, AccountInterface $account) {
    $this->titleResolver = $title_resolver;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('title_resolver'),
      $container->get('current_user')
    );
  }

  /**
   * Returns a unique string identifying the form.
   */
  public function getFormId() {
    return 'did_this_help_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'did_this_help/did_this_help';
    $form['#attributes']['class'][] = 'did-this-help';
    // @todo Use DI instead.
    $config = \Drupal::config('did_this_help.settings');
    $form['question']['#markup'] = '<div class="question">' . $config->get('question') . '</div>';
    $form['yes'] = [
      '#type' => 'submit',
      '#value' => $this->t('Yes'),
      '#ajax' => [
        'callback' => '::sendAjaxForm',
        'disable-refocus' => FALSE,
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Sending information'),
        ],
      ],
    ];
    $form['no'] = [
      '#type' => 'submit',
      '#value' => $this->t('No'),
    ];
    $form['no_choice_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'no-choice-wrapper',
        ],
      ],
      '#weight' => 10,
    ];

    $form['no_choice_wrapper']['title']['#markup'] = '<h3>' . t('Why wasn\'t this information helpful') . '</h3>';
    $no_answers = $config->get('no_answers');

    $no_choice_list = explode(PHP_EOL, $no_answers);
    $no_choice_list[] = $this->t('Other');

    $form['no_choice_wrapper']['no_list'] = [
      '#type' => 'radios',
      '#options' => $no_choice_list,
      '#title' => '',
      '#attributes' => [
        'class' => [
          'no-list',
        ],
      ],
    ];

    $form['no_choice_wrapper']['message'] = [
      '#title' => $this->t('Tell us more'),
      '#title_display' => 'invisible',
      '#type' => 'textarea',
      '#rows' => 3,
      '#attributes' => [
        'placeholder' => $this->t('Tell us more - please don’t include personal info like your email or account numbers.'),
        'maxlength' => 250,
      ],
      '#description' => $this->t('Limit to 250 characters.'),
    ];

    // @todo Rewrite with DI.
    $request = \Drupal::request();
    $route_match = \Drupal::routeMatch();
    $title = $this->titleResolver->getTitle($request, $route_match->getRouteObject());
    if (empty($title)) {
      $title = '';
    }
    $request = \Drupal::request();
    $route_match = \Drupal::routeMatch();
    $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
    $form['title'] = [
      '#type' => 'hidden',
      '#value' => $title,
    ];
    $form['path'] = [
      '#type' => 'hidden',
      '#value' => \Drupal::service('path.current')->getPath(),
    ];
    $form['no_choice_wrapper']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#ajax' => [
        'callback' => '::sendAjaxForm',
        'disable-refocus' => FALSE,
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Sending information'),
        ],
      ],
    ];

    return $form;
  }

  /**
   * Submit callback.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Submit ajax callback.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function sendAjaxForm(array &$form, FormStateInterface $form_state) {
    $operation = $form_state->getValue('op');
    if (empty($operation)) {
      // @todo ajax display error.
    }
    $action = $operation->getUntranslatedString();

    $response = new AjaxResponse();
    $data = [];
    $data['uid'] = $this->account->id();
    $data['path'] = $form_state->getValue('path');
    $data['title'] = $form_state->getValue('title');
    switch ($action) {
      case 'Yes':
        $data['choice'] = 'Yes';
        $data['choice_no'] = '';
        $data['message'] = '';
        _did_this_help_send_info($data);
        $elem = [
          '#markup' => t('Thank you for your feedback.'),
        ];
        $response->addCommand(new ReplaceCommand('form[id^=did-this-help-form]', $elem));
        break;

      case 'Send':
        $data['choice'] = 'No';
        $choice_no = $form_state->getValue('no_list');
        $choice_no_string = $form['no_choice_wrapper']['no_list']['#options'][$choice_no];
        $data['choice_no'] = $choice_no_string;
        $data['message'] = $form_state->getValue('message');
        _did_this_help_send_info($data);
        $elem = [
          '#markup' => t('Thank you for your feedback.'),
        ];
        $response->addCommand(new ReplaceCommand('form[id^=did-this-help-form]', $elem));
        break;

      default:
        $elem = [
          '#markup' => t('Something went wrong. Try to reload page and send your feedback again.'),
        ];
        $response->addCommand(new ReplaceCommand('form[id^=did-this-help-form]', $elem));
        break;
    }
    return $response;
  }

}
