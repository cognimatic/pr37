<?php

namespace Drupal\viewer;

use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Component\Utility\Random;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Config\ConfigFactory;

/**
 * ViewerModule service contains code for the .module file.
 *
 * @ingroup viewer
 */
class ViewerModule {

  use StringTranslationTrait;

  /**
   * Date formatter service.
   *
   * @var Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Request stack service.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Module handler service.
   *
   * @var Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Current route match service.
   *
   * @var Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Viewer module service constructor.
   */
  public function __construct(DateFormatter $date_formatter,
    RequestStack $request_stack,
    ModuleHandler $module_handler,
    CurrentRouteMatch $current_route_match,
    ConfigFactory $config_factory) {
    $this->dateFormatter = $date_formatter;
    $this->requestStack = $request_stack;
    $this->moduleHandler = $module_handler;
    $this->currentRouteMatch = $current_route_match;
    $this->configFactory = $config_factory;
  }

  /**
   * Get import frequencies.
   */
  public function getImportFrequencies() {
    $options = [
      300, 600, 900, 1800, 3600, 10800,
      21600, 43200, 86400, 604800, 2629743, 31556926,
    ];
    return [0 => $this->t('Manual')]
      + array_map([$this->dateFormatter, 'formatInterval'],
      array_combine($options, $options));
  }

  /**
   * Page attachements.
   */
  public function getPageAttachments(&$attachments) {
    // This path is needed for manipulations in JS assets.
    $base_url = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
    $module_path = $this->moduleHandler->getModule('viewer')->getPath();
    $attachments['#attached']['drupalSettings']['viewer']['path'] = $base_url . '/' . $module_path . '/';
  }

  /**
   * Preprocess html.
   */
  public function getPreprocessHtml(&$page) {
    // Hide admin toolbar from the preview page.
    if ($this->currentRouteMatch->getRouteName() == 'entity.viewer.iframe_preview_src') {
      $page['page_top']['toolbar']['#access'] = FALSE;
    }
  }

  /**
   * Prepare email.
   */
  public function getMail($key, &$message, $params) {
    if ($key == 'notification') {
      $message['from'] = $this->configFactory->get('system.site')->get('mail');
      $message['subject'] = $params['subject'];
      $message['body'][] = $params['message'];
    }
  }

  /**
   * Hook_theme() helper.
   */
  public function getTheme() {
    return [
      'viewer_table' => [
        'variables' => [
          'uuid' => NULL,
          'viewer' => NULL,
          'settings' => [],
          'last_import' => NULL,
        ],
        'base hook' => 'viewer_plugin',
        'preprocess functions' => ['viewer_viewer_plugin_preprocess'],
      ],
      'viewer_footable' => [
        'variables' => [
          'uuid' => NULL,
          'viewer' => NULL,
          'settings' => [],
          'last_import' => NULL,
        ],
        'base hook' => 'viewer_plugin',
        'preprocess functions' => ['viewer_viewer_plugin_preprocess'],
      ],
      'viewer_spreadsheet' => [
        'variables' => [
          'uuid' => NULL,
          'viewer' => NULL,
          'settings' => [],
          'configuration' => [],
          'last_import' => NULL,
        ],
        'base hook' => 'viewer_plugin',
        'preprocess functions' => ['viewer_viewer_plugin_preprocess'],
      ],
      'viewer_pdfjs' => [
        'variables' => [
          'uuid' => NULL,
          'viewer' => NULL,
          'settings' => [],
          'last_import' => NULL,
        ],
        'base hook' => 'viewer_plugin',
        'preprocess functions' => ['viewer_viewer_plugin_preprocess'],
      ],
      'viewer_tabs' => [
        'variables' => [
          'uuid' => NULL,
          'viewer' => NULL,
          'settings' => [],
          'tabs' => [],
        ],
        'base hook' => 'viewer_plugin',
        'preprocess functions' => ['viewer_viewer_plugin_preprocess'],
      ],
      'viewer_accordion' => [
        'variables' => [
          'uuid' => NULL,
          'viewer' => NULL,
          'settings' => [],
          'accordion' => [],
        ],
        'base hook' => 'viewer_plugin',
        'preprocess functions' => ['viewer_viewer_plugin_preprocess'],
      ],
      'viewer_chartjs' => [
        'variables' => [
          'uuid' => NULL,
          'viewer' => NULL,
          'wrapper' => NULL,
          'type' => NULL,
          'labels' => NULL,
          'settings' => [],
          'last_import' => NULL,
        ],
        'base hook' => 'viewer_plugin',
        'preprocess functions' => ['viewer_viewer_plugin_preprocess'],
      ],
      'viewer_apexchart' => [
        'variables' => [
          'uuid' => NULL,
          'viewer' => NULL,
          'wrapper' => NULL,
          'type' => NULL,
          'labels' => NULL,
          'settings' => [],
          'last_import' => NULL,
        ],
        'base hook' => 'viewer_plugin',
        'preprocess functions' => ['viewer_viewer_plugin_preprocess'],
      ],
      'viewer_datatables' => [
        'variables' => [
          'uuid' => NULL,
          'viewer' => NULL,
          'settings' => [],
          'last_import' => NULL,
        ],
        'base hook' => 'viewer_plugin',
        'preprocess functions' => ['viewer_viewer_plugin_preprocess'],
      ],
      'viewer_fullcalendar' => [
        'variables' => [
          'uuid' => NULL,
          'viewer' => NULL,
          'settings' => [],
          'configuration' => [],
          'last_import' => NULL,
        ],
        'base hook' => 'viewer_plugin',
        'preprocess functions' => ['viewer_viewer_plugin_preprocess'],
      ],
      'viewer_support' => [
        'variables' => [],
      ],
      'page__admin__structure__viewers__iframe_src' => [
        'template' => 'page--admin--structure--viewers--iframe-src',
        'base hook' => 'page',
      ],
    ];
  }

  /**
   * Viewer plugin preprocess function.
   */
  public function getViewerPluginPreprocess(&$variables) {
    $variables['title'] = !empty($variables['settings']['title']) ? $variables['settings']['title'] : '';
    $variables['subtitle'] = !empty($variables['settings']['subtitle']) ? $variables['settings']['subtitle'] : '';
    $variables['header_summary'] = !empty($variables['settings']['header_summary']) ? $variables['settings']['header_summary'] : '';
    $variables['footer_summary'] = !empty($variables['settings']['footer_summary']) ? $variables['settings']['footer_summary'] : '';
    if (!empty($variables['settings']['last_import_format'])) {
      $variables['last_import_formatted'] = \Drupal::service('date.formatter')
        ->format($variables['last_import'], $variables['settings']['last_import_format']);
      $variables['last_import_position'] = !empty($variables['settings']['last_import_position']) ? $variables['settings']['last_import_position'] : '';
      $variables['last_import_output'] = str_replace('@date', $variables['last_import_formatted'], $variables['settings']['last_import']);
    }
    $variables['random'] = md5((new Random())->string(8, TRUE));
  }

}
