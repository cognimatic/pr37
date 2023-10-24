<?php

namespace Drupal\civiccookiecontrol\Form;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\civiccookiecontrol\CCC9Vendors;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Form\FormState;
use Drupal\Core\Locale\CountryManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Routing\RouteBuilderInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The configuration form for cookie control settings.
 */
class IAB2Settings extends ConfigFormBase {

  /**
   * Country manager object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\Drupal\Core\Locale\CountryManager
   */
  protected $countryManager;

  /**
   * Cache backend object.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Router builder object.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * The list of available modules.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $extensionListModule;

  /**
   * Guzzle\Client instance.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * LoggerChannelFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * IAB2Settings constructor.
   *
   * @param \Drupal\Core\Locale\CountryManager $countryManager
   *   Injected CountyManager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Injected config factory service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Injected cache service.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $routeBuilder
   *   Injected router builder service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   *   List of available modules.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The http_client.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerFactory
   *   The logger.
   */
  public function __construct(
        CountryManager $countryManager,
        ConfigFactoryInterface $config_factory,
        CacheBackendInterface $cache,
        RouteBuilderInterface $routeBuilder,
        ModuleExtensionList $extension_list_module,
        ClientInterface $http_client,
        LoggerChannelFactory $loggerFactory,
    ) {
    parent::__construct($config_factory);
    $this->countryManager = $countryManager;
    $this->cache = $cache;
    $this->routerBuilder = $routeBuilder;
    $this->extensionListModule = $extension_list_module;
    $this->httpClient = $http_client;
    $this->loggerFactory = $loggerFactory;
    civiccookiecontrol_check_cookie_categories();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('country_manager'),
      $container->get('config.factory'),
      $container->get('cache.data'),
      $container->get('router.builder'),
      $container->get('extension.list.module'),
      $container->get('http_client'),
      $container->get('logger.factory'),
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iab2_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $configData = $this->config(CCCConfigNames::IAB2)->get();
    foreach ($configData as $key => $configValue) {
      if (strpos($key, 'iab') !== FALSE) {
        if (is_array($form_state->getValue($key)) && array_key_exists('format', $form_state->getValue($key))) {
          $this->config(CCCConfigNames::IAB2)->set($key, $form_state->getValue($key)['value']);
        }
        elseif (strpos($key, 'Text') !== FALSE) {
          if ($form_state->getValue($key) != '') {
            $this->config(CCCConfigNames::IAB2)->set($key, str_replace([
              "\r\n",
              "\n",
              "\r",
            ], '', $form_state->getValue($key)))->save();
          }
        }
        else {
          $this->config(CCCConfigNames::IAB2)->set($key, $form_state->getValue($key))->save();
        }
      }
    }
    $this->cache->delete('civiccookiecontrol_config');
    $this->routerBuilder->rebuild();
    drupal_flush_all_caches();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [CCCConfigNames::IAB2];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['iab'] = [
      '#type' => 'details',
      '#title' => $this->t('IAB V2.0 Settings'),
      '#open' => TRUE,
    ];

    $form['iab']['iabCMP'] = [
      '#type' => 'radios',
      '#title' => $this->t("Enable IAB (TCF V2.2) Support."),
      '#options' => [
        TRUE => $this->t("Yes"),
        FALSE => $this->t('No'),
      ],
      '#default_value' => $this->config(CCCConfigNames::IAB2)->get('iabCMP') ? 1 : 0,
      '#description' => $this->t("Whether or not Cookie Control supports the IAB's TCF v2.2."),
    ];

    $form['iab']['iabSettings'] = [
      '#type' => 'details',
      '#title' => $this->t('IAB Settings'),
      '#open' => FALSE,
      '#states' => [
      // Action to take.
        'invisible' => [
          ':input[name=iabCMP]' => [
            'value' => 0,
          ],
        ],
      ],
    ];

    $this->loadIabSettings($form);
    $this->loadVendorSettings($form);

    $form_state->setCached(FALSE);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save IAB (TCF v2.0) Configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Loads IAB 2.0 form elements from the corresponding twig file.
   *
   * @param array $form
   *   The form.
   */
  protected function loadIabSettings(&$form) {
    $iabYamlPath = $this->extensionListModule->getPath('civiccookiecontrol') . "/src/Form/IAB2FormElements/iab.settings.yml";
    $formItems = Yaml::parse(file_get_contents($iabYamlPath));
    foreach ($formItems as $key => $element) {
      if (array_key_exists('#format', $element) && $element['#format'] == 'cookie_control_html') {
        $element['#allowed_formats'] = [$element['#format']];
      }

      if (($element['#default_value'] == $key) &&
          (array_key_exists('boolOptions', $element) && $element['boolOptions'] == 1)) {
        $element['#options'] = [
          TRUE => 'Yes',
          FALSE => 'No',
        ];
        $element['#default_value'] = $this->config(CCCConfigNames::IAB2)->get($key) ? 1 : 0;
      }
      elseif ($element['#default_value'] == $key) {
        $element['#default_value'] = $this->config(CCCConfigNames::IAB2)->get($key);
      }
      unset($element['boolOptions']);
      if ($key == 'iabIncludeVendors') {
        $form['iab']['iabSettings']['vendor'][$key] = $element;
      }
      else {
        $form['iab']['iabSettings'][$key] = $element;
      }
    }
  }

  /**
   * Loads Vendor options.
   *
   * @param array $form
   *   The form.
   */
  protected function loadVendorSettings(&$form) {

    $form['iab']['iabSettings']['vendor'] = [
      '#type' => 'details',
      '#title' => $this->t('Vendor Options'),
      '#open' => FALSE,
      '#description' => $this->t('An emphasis of TCF v2.2 is to allow site owners (Publishers)
      the ability to limit the number of vendors detailed
      by the CMP and avoid requesting user’s consent for
      Vendors that operate in technical environments and jurisdictions that are not relevant to their online services.
      This can be achieved via the text field `Vendor ids`.
      To customise and limit which vendors are displayed, the `Vendor ids` text field needs to be
      populated with a comma separate list of the vendor ids you wish to support.
      To make this more convenient, we have created the tool
      below where you can set the territorial scopes, environments and service types you are interested
      in and the tool will find all vendors operating within these parameters and create the array of their ids for you.
      '),
    ];
    $form['iab']['iabSettings']['vendor']['territorial_scope'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Territorial Scope'),
      '#options' => CCC9Vendors::TERRITORIAL_SCOPE,
      '#description' => $this->t('Select one or more Territorial Scope(s).'),
    ];
    $form['iab']['iabSettings']['vendor']['environments'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Environments'),
      '#options' => array_combine(CCC9Vendors::ENVIRONMENTS, CCC9Vendors::ENVIRONMENTS),
      '#description' => $this->t('Select one or more Environemnts.'),
    ];
    $form['iab']['iabSettings']['vendor']['services_type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Services type'),
      '#options' => array_combine(CCC9Vendors::SERVICE_TYPES, CCC9Vendors::SERVICE_TYPES),
      '#description' => $this->t('Select one or more Service Type(s).'),
    ];
    $form['iab']['iabSettings']['vendor']['iabIncludeVendors'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Vendor ids'),
      '#default_value' => $this->config(CCCConfigNames::IAB2)->get('iabIncludeVendors'),
      '#description' => $this->t('The list of vendor ids that you wish to seek consent for. When left empty, consent will sought for all IAB TCF vendors.'),
    ];
    $form['iab']['iabSettings']['vendor']['message']['#markup'] = "<div id='message-wrapper'> </div>";
    $form['iab']['iabSettings']['vendor']['generate'] = [
      '#type' => 'button',
      '#prefix' => '<br>',
      '#value' => $this->t('Calculate list of vendor ids based on my selections above.'),
      '#ajax' => [
        'callback' => [$this, 'generateVendors'],
        'wrapper' => 'message-wrapper',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Calcualting...'),
        ],
      ],
    ];
  }

  /**
   * Generate vendors using user's options.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormState $formState
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The response.
   */
  public function generateVendors(array $form, FormState $formState) {

    $request = $this->getVendorsList();

    $response = new AjaxResponse();

    // Get response status.
    $status = is_object($request) ? $request->getStatusCode() : $request;

    if ($status == 200) {
      $responseArray = json_decode($request->getBody()->getContents(), TRUE);
      $vendorsArray = $responseArray['vendors'];

      // Get form values.
      $territorialScope = !empty(array_filter(($formState->getValue('territorial_scope')))) ? array_map('strtolower', array_filter($formState->getValue('territorial_scope'))) : NULL;
      $servicesType = !empty(array_filter(($formState->getValue('services_type')))) ? array_map('strtolower', array_filter($formState->getValue('services_type'))) : NULL;
      $environments = !empty(array_filter(($formState->getValue('environments')))) ? array_map('strtolower', array_filter($formState->getValue('environments'))) : NULL;

      $filters = [
        'territorialScope' => $territorialScope,
        'serviceTypes' => $servicesType,
        'environments' => $environments,
      ];

      $output = [];
      array_walk($vendorsArray, function ($element) use ($filters, &$output) {
        $matches = TRUE;
        foreach ($filters as $key => $value) {
          if ($value) {
            if ((isset($element[$key]) && !array_intersect($value, array_map('strtolower', $element[$key]))) || !isset($element[$key])) {
              $matches = FALSE;
            }
          }
        }

        if ($matches) {
          $output[] = $element['id'];
        }
      });

      // Sorting array.
      sort($output);

      // Add vendors array ids as value in rel field, and create a message.
      $response->addCommand(new InvokeCommand('#edit-iabincludevendors', 'val', [$output]));
      $response->addCommand(new HtmlCommand('#message-wrapper', '<div class = "messages messages--status">' . count($output) . ' vendors added</div>'));

    }
    else {
      // Can't retrieve vendors list, log error and create a warning message.
      $output = '<div class = "messages messages--warning">Cannot retrieve vendors. Please try again later</div>';
      $response->addCommand(new HtmlCommand('#message-wrapper', $output));
    }

    return $response;
  }

  /**
   * Get vendor list JSON from cdn.
   *
   * @return int|mixed|\Psr\Http\Message\ResponseInterface
   *   Response object or http error
   */
  protected function getVendorsList() {

    $url = 'https://cc.cdn.civiccomputing.com/vl/v2/additional-vendor-information-list.json';
    $client = $this->httpClient;
    $parameters = [
      'headers' => [
        'Content-Type' => 'application/json',
      ],
    ];

    try {
      $response = $client->get($url, $parameters['headers']);
      return $response;

    }
    catch (GuzzleException $ex) {

      // Log errors.
      $this->loggerFactory->get('civiccookiecontrol')->error($ex->getMessage());

      // Return response code.
      return $ex->getCode();

    }

  }

}
