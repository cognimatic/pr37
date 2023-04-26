<?php

namespace Drupal\viewer\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;
use Drupal\viewer\Plugin\ViewerManager;

/**
 * Resolves "viewer" type parameters in routes.
 */
final class ViewerPluginConverter implements ParamConverterInterface {

  /**
   * Viewer manager.
   *
   * @var \Drupal\viewer\Plugin\ViewerManager
   */
  private $viewerManager;

  /**
   * ViewerPluginConverter constructor.
   *
   * @param \Drupal\viewer\Plugin\ViewerManager $viewer
   *   The Viewer plugin manager.
   */
  public function __construct(ViewerManager $viewer) {
    $this->viewerManager = $viewer;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $not_found = new NotFoundHttpException();
    try {
      $viewer = $this->viewerManager->createInstance(str_replace('-', '_', $value));
      if ($viewer->requirementsAreMet()) {
        return $viewer;
      }
      else {
        throw $not_found;
      }
    }
    catch (PluginNotFoundException $e) {
      throw $not_found;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'viewer');
  }

}
