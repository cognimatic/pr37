<?php

namespace Drupal\civiccookiecontrol\Form\Steps;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\civiccookiecontrol\Form\Buttons\CCCLicenseInfoButton;
use Drupal\civiccookiecontrol\Form\CCCFormHelper;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Locale\CountryManager;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * License info step.
 */
class CCCLicenseInfo extends CCCBaseStep {
  use StringTranslationTrait;
  use DependencySerializationTrait;
  use MessengerTrait;

  /**
   * The configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(
        CountryManager $countryManager,
        ConfigFactoryInterface $config,
        FileSystemInterface $fileSystem,
        PrivateTempStoreFactory $tempStoreFactory
    ) {
    $this->countryManager = $countryManager;
    $this->fileSystem = $fileSystem;
    $this->tempStore = $tempStoreFactory->get('civiccookiecontrol');
    $this->config = $config->getEditable(CCCConfigNames::COOKIECONTROL);
    $this->messenger()->deleteByType($this->messenger::TYPE_ERROR);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('country_manager'),
          $container->get('config.factory'),
          $container->get('file_system'),
          $container->get('tempstore.private')
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function setStep() {
    return CCCStepsEnum::CCC_LICENSE_INFO;
  }

  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements() {

    $form['license_info'] = [
      '#type' => 'details',
      '#title' => $this->t('Your Cookie Control License Information'),
      '#open' => TRUE,
    ];

    $form['license_info']['civiccookiecontrol_api_key_version'] = [
      '#type' => 'radios',
      '#title' => $this->t('Cookie Control Version'),
      '#options' => [
        8 => $this->t('Version 8'),
        9 => $this->t('Version 9'),
      ],
      '#default_value' => $this->getValues()['civiccookiecontrol_api_key_version'] ??
      $this->config->get('civiccookiecontrol_api_key_version'),
      '#required' => TRUE,
      '#description' => $this->t('Version of obtained Cookie Control API Key.'),
      '#ajax' => [
        'callback' => [$this, 'updateCookieVersion'],
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Updating cookie version...'),
        ],
      ],
    ];

    $form['license_info']['civiccookiecontrol_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key Text'),
      '#default_value' => $this->getValues()['civiccookiecontrol_api_key'] ??
      $this->config->get('civiccookiecontrol_api_key'),
      '#required' => TRUE,
      '#suffix' => '<span class="api-validation"></span>',
      '#attributes' => [
        'class' => [
          'container-inline',
        ],
      ],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [$this, 'checkApiKey'],
        'event' => 'change',
        'wrapper' => 'api-validation',
        'effect' => 'fade',
        'method' => 'append',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Validating Api key...'),
        ],
      ],
      '#description' => $this->t('The API Key received for your deployment of Cookie Control.
        If in doubt, please contact the Civic helpdesk on helpdesk@civicuk.com,
        and please include the email and the domain you registered with in your email.'),
    ];

    $form['license_info']['civiccookiecontrol_product'] = [
      '#type' => 'radios',
      '#title' => $this->t('Product License Type'),
      '#options' => [
        'COMMUNITY' => $this->t('Community Edition'),
        'PRO' => $this->t('Pro Edition'),
        'PRO_MULTISITE' => $this->t('Pro Multisite Edition'),
      ],
      '#default_value' => $this->getValues()['civiccookiecontrol_product'] ??
      $this->config->get('civiccookiecontrol_product'),
      '#required' => TRUE,
      '#description' => $this->t('The type of obtained Cookie Control License that is tied to your API Key.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      new CCCLicenseInfoButton(FALSE),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldNames() {
    return [
      'civiccookiecontrol_api_key_version',
      'civiccookiecontrol_api_key',
      'civiccookiecontrol_product',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function updateCookieVersion(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state->getTriggeringElement()['#name'] == 'civiccookiecontrol_api_key_version') {
      $this->config->set(
            "civiccookiecontrol_api_key_version",
            $form['wrapper']['license_info']['civiccookiecontrol_api_key_version']['#value']
        )
        ->save();

      if ($this->config->get('civiccookiecontrol_initial_state') == 'BOX') {
        $this->config->set("civiccookiecontrol_api_key_version", "OPEN");
      }
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function checkApiKey(array $form, FormStateInterface &$form_state) {
    $api_key_value = $form['wrapper']['license_info']['civiccookiecontrol_api_key']['#value'];

    if ($form_state->getTriggeringElement()['#name'] == 'civiccookiecontrol_api_key' && !empty($api_key_value)) {
      $response = new AjaxResponse();
      $key = CCCFormHelper::validateApiKey($api_key_value);
      if ($key !== FALSE) {
        $this->apiKeyValidated = TRUE;
        $response->addCommand(
              new InvokeCommand(
                  "#edit-civiccookiecontrol-product-" . strtolower(
                      str_replace("_", "-", $key)
                  ),
                  'loadLicense',
                  [$key]
              )
          );
        $msg = '<div><strong>' .
                  $this->t('Valid API Key. Click "Customize cookie control" to proceed.') .
                  '</strong></div>';
        $response->addCommand(new HtmlCommand(".api-validation", $msg));
      }
      else {
        $this->apiKeyValidated = FALSE;
        $errMsg = '<div class="form-item--error-message"><strong>' .
                  $this->t('Please provide a valid API key.
                  For further information please contact Civic at queries@civicuk.com') .
                  '</strong></div>';
        $response->addCommand(new HtmlCommand(".api-validation", $errMsg));
      }

      $form_state->setRebuild(TRUE);
      return $response;
    }
  }

}
