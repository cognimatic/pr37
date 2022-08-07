<?php

namespace Drupal\cl_components\Controller;

use Drupal\cl_components\Component\Component;
use Drupal\cl_components\Component\ComponentDiscovery;
use Drupal\cl_components\Component\ComponentMetadata;
use Drupal\cl_components\Exception\InvalidComponentException;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the registry.
 */
final class ComponentAudit extends ControllerBase {

  private ComponentDiscovery $discovery;

  /**
   * @param \Drupal\cl_components\Component\ComponentDiscovery $discovery
   */
  public function __construct(ComponentDiscovery $discovery) {
    $this->discovery = $discovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $discovery = $container->get(ComponentDiscovery::class);
    assert($discovery instanceof ComponentDiscovery);
    return new static($discovery);
  }

  /**
   * Render the registry.
   *
   * @return array
   *   The render array.
   */
  public function audit(): array {
    $paths = $this->discovery->findAllPaths();
    return [
      'logs' => [
        '#prefix' => '<p>',
        '#markup' => $this->t('Remember to check your logs if you are having problems with your components.'),
        '#suffix' => '</p>',
      ],
      'components' => [
        '#type' => 'container',
        '#attributes' => ['class' => 'panel__container'],
        '#attached' => ['library' => ['cl_components/cl_registry']],
        'panels' => [
          '#theme' => 'item_list',
          '#title' => $this->t('Detected Components'),
          '#items' => array_map([$this, 'buildComponentCard'], $paths),
        ],
        'recommendations' => [
          '#prefix' => '<p>',
          '#markup' => $this->t('We recommend you to add a <code>README.md</code> file and a <code>thumbnail.png</code> to all your components. Other modules and JS tools may use them to provide additional insight.
'),
          '#suffix' => '</p>',
        ],
      ],
    ];
  }

  /**
   * Builds the render array for the component card.
   *
   * @param string $path
   *   The path to the component folder.
   *
   * @return array[]
   *   The card render array.
   */
  private function buildComponentCard(string $path): array {
    try {
      $component = $this->discovery->instantiateComponent($path);
    }
    catch (InvalidComponentException $e) {
      $component = NULL;
      $message = $this->t('Invalid component @path. Error: @error', [
        '@path' => $path,
        '@error' => $e->getMessage(),
      ]);
      return [
        'title' => [
          '#prefix' => '<h4>',
          '#markup' => $this->t('💥 Error in Component'),
          '#suffix' => '</h4>',
        ],
        'path' => [
          '#prefix' => '<pre>',
          '#markup' => $path,
          '#suffix' => '</pre>',
        ],
        'error' => [
          '#prefix' => '<p>',
          '#markup' => $e->getMessage(),
          '#suffix' => '</p>',
        ],
        '#wrapper_attributes' => ['class' => 'panel-item'],
      ];
    }
    $metadata = $component->getMetadata();
    $default_template = $component->getId() . '.twig';
    $template_message = in_array($default_template, $component->getTemplates())
      ? $this->t('✅ %default template is present', ['%default' => $default_template])
      : $this->t('❌ @id.twig is missing', ['@id' => $component->getId()]);
    $missing_variants = array_diff(
      [
        $default_template,
        ...array_map(static fn(string $variant) => sprintf('%s--%s.twig', $component->getId(), $variant), $component->getVariants()),
      ],
      $component->getTemplates()
    );
    $missing_variants = array_filter(
      $missing_variants,
      static fn(string $name) => $name !== $default_template
    );
    $variants_message = empty($missing_variants)
      ? $this->t('✅ All variants have their templates')
      : $this->t('❌ Missing templates: %variants', ['%variants' => implode(', ', $missing_variants)]);
    $assets = $this->discovery->discoverDistAssets($path);
    $assets_message = [
      '#theme' => 'item_list',
      '#items' => array_map(static fn(string $file) => preg_replace('@.*/' . $component->getId() . '/@', '', $file), [
        ...$assets['js'] ?? [],
        ...$assets['css'] ?? [],
      ]),
    ];
    return [
      'title' => [
        '#prefix' => '<h4>',
        '#markup' => $metadata->getName(),
        '#suffix' => '</h4>',
      ],
      'description' => $metadata
        ->getDescription() === ComponentMetadata::DEFAULT_DESCRIPTION ? [] : [
        '#prefix' => '<p>',
        '#markup' => $metadata->getDescription(),
        '#suffix' => '</p>',
      ],
      'path' => [
        '#prefix' => '<pre>',
        '#markup' => $path,
        '#suffix' => '</pre>',
      ],
      'table' => [
        '#theme' => 'table',
        '#header' => [
          $this->t('Metadata'),
          $this->t('Default Template'),
          $this->t('Variants'),
          $this->t('Assets'),
        ],
        '#rows' => [
          [
            $this->t('✅ All metadata is correct'),
            $template_message,
            $variants_message,
            ['data' => $assets_message],
          ],
        ],
      ],
      '#wrapper_attributes' => [
        'class' => 'panel-item',
        'id' => $component->getId(),
      ],
    ];
  }

}
