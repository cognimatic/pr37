<?php

namespace Drupal\civiccookiecontrol\Form;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Locale\CountryManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
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
   */
  public function __construct(
        CountryManager $countryManager,
        ConfigFactoryInterface $config_factory,
        CacheBackendInterface $cache,
        RouteBuilderInterface $routeBuilder,
        ModuleExtensionList $extension_list_module
    ) {
    parent::__construct($config_factory);
    $this->countryManager = $countryManager;
    $this->cache = $cache;
    $this->routerBuilder = $routeBuilder;
    $this->extensionListModule = $extension_list_module;
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
          $container->get('extension.list.module')
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
      $form['iab']['iabSettings'][$key] = $element;
    }
  }

}
