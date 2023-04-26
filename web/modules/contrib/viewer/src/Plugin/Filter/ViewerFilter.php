<?php

namespace Drupal\viewer\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a text filter that turns <viewer> tags into markup.
 *
 * @Filter(
 *   id = "viewer",
 *   title = @Translation("Viewer"),
 *   description = @Translation("Converts &#60;viewer&#62; to Viewer elements."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   weight = 100,
 * )
 *
 * @internal
 */
class ViewerFilter extends FilterBase implements ContainerFactoryPluginInterface, TrustedCallbackInterface {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Renderer $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('renderer'));
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    if (stristr($text, '<viewer') === FALSE) {
      return $result;
    }

    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//viewer') as $element) {
      $storage = \Drupal::entityTypeManager()->getStorage('viewer');
      $ids = $storage->getQuery()
        ->condition('id', $element->getAttribute('data-viewer'))
        ->sort('created', 'DESC')
        ->accessCheck(TRUE)
        ->execute();
      $entities = $storage->loadMultiple($ids);
      if ($viewer = reset($entities)) {
        if ($plugin = $viewer->getViewerPlugin()) {
          $plugin->setViewer($viewer);
          if ($viewer->isPublished()) {
            if ($plugin->requirementsAreMet()) {
              $build['viewer'] = $plugin->getRenderable();
            }
            else {
              $build['viewer'] = [
                '#markup' => $this->t(
                  'Required conditions are not met for the %name',
                  ['%name' => $viewer->label()]
                ),
              ];
            }
          }
          else {
            $build['viewer'] = ['#markup' => $this->t('%name is inactive', ['%name' => $viewer->label()])];
          }
          if (!empty($build['viewer'])) {
            if ($viewer_source = $viewer->getViewerSource()) {
              $build['viewer']['#cache']['tags'] = [
                'viewer_source:' . $viewer_source->id(),
                'viewer:' . $viewer->id(),
              ];
            }
            else {
              $build['viewer']['#cache']['tags'] = ['viewer:' . $viewer->id()];
            }
          }
        }
      }
      // Delete the consumed attributes.
      $element->removeAttribute('data-viewer');
      $this->renderIntoDomNode($build, $element, $result);
    }
    $result->setProcessedText(Html::serialize($dom));
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [];
  }

  /**
   * Renders the given render array into the given DOM node.
   */
  protected function renderIntoDomNode(array $build, \DOMNode $node, FilterProcessResult &$result) {
    $markup = $this->renderer->executeInRenderContext(new RenderContext(), function () use (&$build) {
      return $this->renderer->render($build);
    });
    $result = $result->merge(BubbleableMetadata::createFromRenderArray($build));
    static::replaceNodeContent($node, $markup);
  }

  /**
   * Replaces the contents of a DOMNode.
   */
  protected static function replaceNodeContent(\DOMNode &$node, $content) {
    if (strlen($content)) {
      $replacement_nodes = Html::load($content)->getElementsByTagName('body')
        ->item(0)
        ->childNodes;
    }
    else {
      $replacement_nodes = [$node->ownerDocument->createTextNode('')];
    }

    foreach ($replacement_nodes as $replacement_node) {
      $replacement_node = $node->ownerDocument->importNode($replacement_node, TRUE);
      $node->parentNode->insertBefore($replacement_node, $node);
    }
    $node->parentNode->removeChild($node);
  }

}
