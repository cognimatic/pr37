<?php

namespace Drupal\civiccookiecontrol\Form;

use Drupal\civiccookiecontrol\Access\CookieControlAccess;
use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\civiccookiecontrol\Form\Steps\CCCStepsEnum;
use Drupal\civiccookiecontrol\Form\Steps\CCCStepsManager;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Locale\CountryManager;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Civic cookie control settings configuration form class.
 */
class CivicCookieControlSettings extends ConfigFormBase {

  use MessengerTrait;
  use DependencySerializationTrait;

  /**
   * Country manager object.
   *
   * @var \Drupal\Core\Locale\CountryManager
   */
  protected $countryManager;

  /**
   * Cahche onject.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Router builder object from dependency injection.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * Filesystem object from dependency injection.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Store if api key is validated.
   *
   * @var bool
   */
  private $apiKeyValidated;

  /**
   * Coockie control form elements.
   *
   * @var array
   */
  private $cccFormElements;

  /**
   * PrivateTempStore object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  private $tempStore;

  /**
   * Current step.
   *
   * @var \Drupal\civiccookiecontrol\Form\Steps\CCCBaseStep
   */
  protected $step;

  /**
   * Current step id.
   *
   * @var int
   */
  protected $stepId;

  /**
   * The step manager.
   *
   * @var \Drupal\civiccookiecontrol\Form\Steps\CCCStepsManager
   */
  protected $stepManager;

  /**
   * Entity type manager from dependency injection.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(
        EntityTypeManagerInterface $entityTypeManager,
        CountryManager $countryManager,
        ConfigFactoryInterface $config,
        CacheBackendInterface $cache,
        RouteBuilderInterface $routeBuilder,
        FileSystemInterface $fileSystem,
        PrivateTempStoreFactory $tempStoreFactory,
        CCCStepsManager $stepManager
    ) {

    parent::__construct($config);
    $this->entityTypeManager = $entityTypeManager;
    $this->countryManager = $countryManager;
    $this->cache = $cache;
    $this->routeBuilder = $routeBuilder;
    $this->fileSystem = $fileSystem;
    $this->tempStore = $tempStoreFactory->get('civiccookiecontrol');
    $this->stepManager = $stepManager;
    if (CookieControlAccess::checkApiKey()) {
      $this->stepId = CCCStepsEnum::CCC_SETTINGS;
    }
    else {
      $this->stepId = CCCStepsEnum::CCC_LICENSE_INFO;
    }
    civiccookiecontrol_check_cookie_categories();
    civiccookiecontrol_check_local_mode();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('entity_type.manager'),
          $container->get('country_manager'),
          $container->get('config.factory'),
          $container->get('cache.data'),
          $container->get('router.builder'),
          $container->get('file_system'),
          $container->get('tempstore.private'),
          $container->get('civiccookiecontrol.CCCStepsManager')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'civiccookiecontrol_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [CCCConfigNames::COOKIECONTROL];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save filled values to step. So we can use them as default_value later on.
    $values = [];
    foreach ($this->step->getFieldNames() as $name) {
      $values[$name] = $form_state->getValue($name);
    }

    $this->step->setValues($values /*$form_state->getValues()*/);

    $this->stepManager->addStep($this->step);
    // Set step to navigate to.
    $triggering_element = $form_state->getTriggeringElement();
    $this->stepId = $triggering_element['#goto_step'];

    // If an extra submit handler is set, execute it.
    // We already tested if it is callable before.
    if (isset($triggering_element['#submit_handler'])) {
      $this->{$triggering_element['#submit_handler']}($form, $form_state);
    }

    $form_state->setRebuild(TRUE);
  }

  /**
   * Build form array.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The final form array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['wrapper-messages'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'messages-wrapper',
      ],
    ];

    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'ccc-wrapper',
      ],
    ];

    $this->step = $this->stepManager->getStep($this->stepId);

    $form['wrapper'] += $this->step->buildStepFormElements();

    $form['wrapper']['actions']['#type'] = 'actions';
    $buttons = $this->step->getButtons();
    foreach ($buttons as $button) {
      $form['wrapper']['actions'][$button->getKey()] = $button->build();
      $form['wrapper']['actions'][$button->getKey()] += ['#name' => $button->getKey()];

      if ($button->ajaxify()) {
        // Add ajax to button.
        $form['wrapper']['actions'][$button->getKey()]['#ajax'] = [
          'callback' => [$this, 'loadStep'],
          'wrapper' => 'ccc-wrapper',
          'effect' => 'fade',
        ];
      }

      $callable = [$this, $button->getSubmitHandler()];
      if ($button->getSubmitHandler() && is_callable($callable)) {
        // Attach submit handler to button, so we can execute it later on..
        $form['wrapper']['actions'][$button->getKey()]['#submit_handler'] = $button->getSubmitHandler();
      }
    }

    $form['#attached'] = [
      'library' => [
        'civiccookiecontrol/civiccookiecontrol.admin',
        'civiccookiecontrol/civiccookiecontrol.admin_css',
        'civiccookiecontrol/civiccookiecontrol.minicolors',
        'civiccookiecontrol/civiccookiecontrol.lct',
      ],
    ];

    return $form;
  }

  /**
   * Loads next form step.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function loadStep(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $messages = $this->messenger()->all();
    if (!empty($messages)) {
      // Form did not validate, get messages and render them.
      $messages = [
        '#theme' => 'status_messages',
        '#message_list' => $messages,
        '#status_headings' => [
          'status' => $this->t('Status message'),
          'error' => $this->t('Error message'),
          'warning' => $this->t('Warning message'),
        ],
      ];
      $response->addCommand(new HtmlCommand('#messages-wrapper', $messages));
    }
    else {
      // Remove messages.
      $response->addCommand(new HtmlCommand('#messages-wrapper', ''));
    }

    // Update Form.
    $response->addCommand(new HtmlCommand(
          '#ccc-wrapper',
          $form['wrapper']
      ));

    return $response;
  }

  /**
   * Validate GDPR / CCPA statement node existanse.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param string $key
   *   The configuration key of the element to validate.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function validateStatementNode(array &$form, FormStateInterface $form_state, $key) {
    if ($form_state->getValue([$key]) > 0) {
      $node_storage = $this->entityTypeManager->getStorage('node');
      $node = $node_storage->load($form_state->getValue($key));
      // If no node can be loaded give the user a suitable message prompt.
      if (!$node) {
        $form_state->setErrorByName(
              $key,
              $this->t('The specified privacy policy node id does not exist.
                  Leave blank if you have not yet created a policy page.')
          );
      }
      else {
        if (strpos($key, 'ccpa') === FALSE) {
          $form_state->setValue('civiccookiecontrol_stmt_url', $node->toUrl()
            ->toString());
        }
        else {
          $form_state->setValue('civiccookiecontrol_ccpa_stmt_url', $node->toUrl()
            ->toString());
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('civiccookiecontrol_api_key')) && isset($form['wrapper']['license_info'])) {
      $form_state->setValue('civiccookiecontrol_api_key', $form['wrapper']['license_info']['civiccookiecontrol_api_key']['#value']);
    }

    if ($form_state->getTriggeringElement()['#name'] == 'ccc_license_info') {
      // Check if the API key is provided.
      if ($form_state->getValue(['civiccookiecontrol_api_key']) == '') {
        $form_state->setErrorByName('civiccookiecontrol_api_key', $this->t('Please provide a Cookie Control API key. For further information please contact Civic at queries@civicuk.com'));
      }
      else {
        $prdType = CCCFormHelper::validateApiKey($form_state->getValue(['civiccookiecontrol_api_key']));
        if ($prdType !== FALSE && $prdType != $form_state->getValue(['civiccookiecontrol_product'])) {
          $form_state->setErrorByName('civiccookiecontrol_product', $this->t('Your api key used is valid for @prdType product type. Please select the appropriate option in Product License Type. For further information please contact Civic at queries@civicuk.com.', ['@prdType' => $prdType]));
        }
        elseif ($prdType == FALSE) {
          $form_state->setErrorByName('civiccookiecontrol_api_key', $this->t('Please provide a valid API key. For further information please contact Civic at queries@civicuk.com'));
        }
      }
    }

    if ($form_state->getTriggeringElement()['#name'] == 'ccc_save') {
      // Attempt to load in a specified privacy policy node id.
      try {
        $this->validateStatementNode($form, $form_state, 'civiccookiecontrol_privacynode');
        $this->validateStatementNode($form, $form_state, 'civiccookiecontrol_ccpa_privacynode');
      }
      catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityMalformedException $e) {
        $this->logger('civiccookiecontrol')->notice($e->getMessage());
      }
    }
  }

  /**
   * Submit cookie control settings form values.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function submitValues(array &$form, FormStateInterface $form_state) {
    foreach ($this->step->getFieldNames() as $fieldName) {
      if (strpos($fieldName, 'civiccookiecontrol') !== FALSE) {
        if ($fieldName == 'civiccookiecontrol_title_text' || $fieldName == 'civiccookiecontrol_intro_text' || $fieldName == 'civiccookiecontrol_full_text') {
          if (is_array($form_state->getValue($fieldName)) && array_key_exists('format', $form_state->getValue($fieldName))) {
            $this->config(CCCConfigNames::COOKIECONTROL)->set($fieldName, $form_state->getValue($fieldName)['value']);
          }
          elseif ($form_state->getValue($fieldName) != '') {
            $this->config(CCCConfigNames::COOKIECONTROL)->set($fieldName, str_replace([
              "\r\n",
              "\n",
              "\r",
            ], '', $form_state->getValue($fieldName)))->save();
          }
        }
        else {
          if ($fieldName == 'civiccookiecontrol_api_key' || $fieldName == 'civiccookiecontrol_product') {
            if ($form_state->getValue($fieldName) != $this->config(CCCConfigNames::COOKIECONTROL)->get($fieldName)) {
              // \Drupal::service("router.builder")->rebuild();
              $this->routeBuilder->rebuild();
            }
          }
          if (array_key_exists($fieldName, $form_state->getValues())) {
            if (is_array($form_state->getValue($fieldName)) && array_key_exists('format', $form_state->getValue($fieldName))) {
              $this->config(CCCConfigNames::COOKIECONTROL)->set($fieldName, $form_state->getValue($fieldName)['value']);
            }
            else {
              $this->config(CCCConfigNames::COOKIECONTROL)->set($fieldName, $form_state->getValue($fieldName))
                ->save();
            }
          }
        }
      }
    }
    $this->cache->delete('civiccookiecontrol_config');
    $this->routeBuilder->rebuild();
  }

  /**
   * Flush cookiecontrol configuration form from tempstore.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function clearTempstore(array &$form, FormStateInterface $form_state) {
    civiccookiecontrol_clear_tempstore();
    $this->step->loadFormElements();
  }

}
