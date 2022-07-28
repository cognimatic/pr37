<?php

namespace Drupal\content_readability\Controller;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\StringTranslation\TranslationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;

/**
 * Controller helper for Content Readability.
 *
 * Just used for page titles.
 */
class ContentReadabilityController extends ControllerBase {

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * StringTranslation Service.
   *
   * @var Drupal\Core\StringTranslation\TranslationManager
   */
  protected $stringTranslation;

  /**
   * Content Readability Controller.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The Config Factory service.
   * @param \Drupal\Core\StringTranslation\TranslationManager $stringTranslation
   *   The String Transliterator service.
   */
  public function __construct(ConfigFactory $configFactory, TranslationManager $stringTranslation) {
    $this->configFactory = $configFactory;
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('string_translation')

    );
  }

  /**
   * Page title callback for a node.
   *
   * @param string $profile
   *   Machine name of the Content Readability Profile.
   *
   * @return string
   *   The page title.
   */
  public function pageTitle($profile) {
    $profiles = $this->configFactory->getEditable('content_readability.settings')->get('content_readability_profiles');
    return $this->t('Edit %title Content Profile', ['%title' => $profiles[$profile]['name']]);
  }

}
