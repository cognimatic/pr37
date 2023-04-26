<?php

namespace Drupal\viewer\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;
use Drupal\viewer\Plugin\ViewerTypeManager;

/**
 * Resolves "viewer_type" type parameters in routes.
 */
final class ViewerTypePluginConverter implements ParamConverterInterface {

  /**
   * Viewer type manager.
   *
   * @var \Drupal\viewer\Plugin\ViewerTypeManager
   */
  private $viewerTypeManager;

  /**
   * ViewerTypePluginConverter constructor.
   *
   * @param \Drupal\viewer\Plugin\ViewerTypeManager $viewer_type
   *   The Viewer plugin manager.
   */
  public function __construct(ViewerTypeManager $viewer_type) {
    $this->viewerTypeManager = $viewer_type;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    try {
      $viewer_type = $this->viewerTypeManager->createInstance(str_replace('-', '_', $value));
      return $viewer_type;
    }
    catch (PluginNotFoundException $e) {
      throw new NotFoundHttpException();
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'viewer_type');
  }

}
