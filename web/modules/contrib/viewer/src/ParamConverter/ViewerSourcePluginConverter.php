<?php

namespace Drupal\viewer\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;
use Drupal\viewer\Plugin\ViewerSourceManager;

/**
 * Resolves "viewer_source" type parameters in routes.
 */
final class ViewerSourcePluginConverter implements ParamConverterInterface {

  /**
   * Viewer source manager.
   *
   * @var \Drupal\viewer\Plugin\ViewerSourceManager
   */
  private $viewerSourceManager;

  /**
   * ViewerSourcePluginConverter constructor.
   *
   * @param \Drupal\viewer\Plugin\ViewerSourceManager $viewer_source
   *   The Viewer Source plugin manager.
   */
  public function __construct(ViewerSourceManager $viewer_source) {
    $this->viewerSourceManager = $viewer_source;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $not_found = new NotFoundHttpException();
    try {
      $viewer_source = $this->viewerSourceManager->createInstance(str_replace('-', '_', $value));
      if ($viewer_source->requirementsAreMet()) {
        return $viewer_source;
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
    return (!empty($definition['type']) && $definition['type'] == 'viewer_source');
  }

}
