<?php

namespace Drupal\prod_check\Plugin\ProdCheck\Modules;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\prod_check\Plugin\ProdCheck\ProdCheckBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns a list of installed modules (and their updates).
 *
 * @ProdCheck(
 *   id = "modulelist",
 *   title = @Translation("Module List"),
 *   category = "modules",
 * )
 */
class ModuleList extends ProdCheckBase {

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The list of installed modules and their updates.
   *
   * @var array
   */
  private $moduleList;

  /**
   * Indicates if the list of installed modules and their updates has been
   * refreshed.
   *
   * @var bool
   */
  private $moduleListRefreshed;

  /**
   * DatabaseUpdates constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $destination
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $generator
   * @param \Drupal\Core\Config\ConfigFactoryInterface $factory
   * @param \Drupal\Core\Datetime\DateFormatter $formatter
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $handler
   */
  public function __construct(
    array $configuration,
  $plugin_id,
  $plugin_definition,
    RedirectDestinationInterface $destination,
    LinkGeneratorInterface $generator,
  ConfigFactoryInterface $factory,
    DateFormatter $formatter,
  ModuleHandlerInterface $handler
  ) {
    $this->moduleHandler = $handler;
    parent::__construct(
      $configuration, $plugin_id, $plugin_definition, $destination, $generator,
      $factory, $formatter, $handler
    );
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @return static
   */
  public static function create(
    ContainerInterface $container,
  array $configuration,
  $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('redirect.destination'),
      $container->get('link_generator'),
      $container->get('config.factory'),
      $container->get('date.formatter'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $build = [
      '#theme' => 'update_report',
    ];

    if (!function_exists('update_get_available')) {
      return $build;
    }

    if ($this->moduleHandler->moduleExists('update') && $available = update_get_available(TRUE)) {
      $this->moduleHandler->loadInclude('update', 'compare.inc');
      $private_key = \Drupal::service('private_key')->get();

      $build['#data'] = [
        'projects' => update_calculate_project_data($available),
        'site_key' => $private_key,
        'last_update' => (int) $_SERVER['REQUEST_TIME'],
      ];

      $this->moduleListRefreshed = TRUE;
      $this->moduleList = $build['#data'];
    }

    return $build;
  }

  /**
   *
   */
  public function data() {
    return $this->moduleList;
  }

  /**
   * Calculates the state for the check.
   *
   * @return bool
   *   TRUE if the check passed
   *   FALSE otherwise
   */
  public function state() {
    return $this->moduleListRefreshed;
  }

  /**
   * {@inheritdoc}
   */
  public function severity() {
    return $this->processor->error();
  }

  /**
   * Returns the success messages for the check.
   *
   * @return array
   *   An associative array containing the following keys
   *     - value: the value of the check
   *     - description: the description of the check
   */
  public function successMessages() {
    return [
      'value' => $this->t('List of modules successfully refreshed'),
      'description' => $this->t('The list of modules has successfully been refreshed.'),
    ];
  }

  /**
   * Returns the fail messages for the check.
   *
   * @return array
   *   An associative array containing the following keys
   *     - value: the value of the check
   *     - description: the description of the check
   */
  public function failMessages() {
    return [
      'value' => $this->t('List of modules failed to refresh'),
      'description' => $this->t('An error occurred while refreshing the list of modules.'),
    ];
  }

}
