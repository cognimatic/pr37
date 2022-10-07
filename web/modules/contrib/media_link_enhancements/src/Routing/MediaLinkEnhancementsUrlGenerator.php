<?php

namespace Drupal\media_link_enhancements\Routing;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\RouteProcessor\OutboundRouteProcessorInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Routing\UrlGenerator;
use Drupal\Core\StreamWrapper\PublicStream;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface;

/**
 * Overrides the core url_generator.non_bubbling service.
 *
 * This class override handles rewriting media links so that they
 * point directly to files and remote video URLs.
 */
class MediaLinkEnhancementsUrlGenerator extends UrlGenerator {

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $provider;

  /**
   * The request context service.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $context;

  /**
   * A request stack object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The path processor to convert the system path to one suitable for urls.
   *
   * @var \Drupal\Core\PathProcessor\OutboundPathProcessorInterface
   */
  protected $pathProcessor;

  /**
   * The route processor.
   *
   * @var \Drupal\Core\RouteProcessor\OutboundRouteProcessorInterface
   */
  protected $routeProcessor;

  /**
   * Entity type manager which performs the upcasting in the end.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Helper service.
   *
   * @var \Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface
   */
  protected $helper;

  /**
   * Override the parent constructor.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $provider
   *   The route provider to be searched for routes.
   * @param \Drupal\Core\PathProcessor\OutboundPathProcessorInterface $path_processor
   *   The path processor to convert the system path to one suitable for urls.
   * @param \Drupal\Core\RouteProcessor\OutboundRouteProcessorInterface $route_processor
   *   The route processor.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   A request stack object.
   * @param string[] $filter_protocols
   *   An array of protocols allowed for URL generation.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface $helper
   *   Helper service.
   */
  public function __construct(RouteProviderInterface $provider, OutboundPathProcessorInterface $path_processor, OutboundRouteProcessorInterface $route_processor, RequestStack $request_stack, array $filter_protocols, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, MediaLinkEnhancementsHelperInterface $helper) {
    $this->provider = $provider;
    $this->context = new RequestContext();
    $this->pathProcessor = $path_processor;
    $this->routeProcessor = $route_processor;
    UrlHelper::setAllowedProtocols($filter_protocols);
    $this->requestStack = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory->get('media_link_enhancements.settings');
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public function generateFromRoute($name, $parameters = [], $options = [], $collect_bubbleable_metadata = FALSE) {

    $options += ['prefix' => ''];
    if (!isset($options['query']) || !is_array($options['query'])) {
      $options['query'] = [];
    }

    $route = $this->getRoute($name);
    $generated_url = $collect_bubbleable_metadata ? new GeneratedUrl() : NULL;

    // Handle direct linking for media.
    // This and the added injected services are the only differences in this
    // file versus the core class we're overriding.
    $directLinkingEnabled = !empty($this->configFactory->get('enable_direct_linking'));

    // Detect media URL lookups from Linkit and allow them to process normally.
    if (isset($parameters['linkit_matcher'])) {
      // Unset the flag so it's not included in the query string.
      unset($parameters['linkit_matcher']);
    }
    else {
      if ($directLinkingEnabled && $name === 'entity.media.canonical' && isset($parameters['media'])) {
        $storage = $this->entityTypeManager->getStorage('media');
        $media = $storage->load($parameters['media']);

        if ($media && $media->isPublished() && $this->helper->checkBundle($media->bundle(), 'direct_linking_bundles')) {
          if ($media->getSource()->getPluginId() === 'oembed:video') {
            // Override the link to remote videos so that the URL points
            // directly to the provider URL.
            $url = $media->getSource()->getSourceFieldValue($media);
            return $collect_bubbleable_metadata ? $generated_url->setGeneratedUrl($url) : $url;
          }
          else {
            $storage = $this->entityTypeManager->getStorage('file');
            $file = $storage->load($media->getSource()->getSourceFieldValue($media));
            if ($file) {

              $filename = $file->getFileName();
              $extension = pathinfo($filename, PATHINFO_EXTENSION);

              if ($this->helper->checkExtension($extension, 'direct_linking_extensions')) {
                $url = str_replace('public://', '', $file->getFileUri());
                // Override links to file-based media so the URL points
                // directly to the file path.
                $url = '/' . PublicStream::basePath() . '/' . $url;
                return $collect_bubbleable_metadata ? $generated_url->setGeneratedUrl($url) : $url;
              }
            }
          }
        }
      }
    }

    $fragment = '';
    if (isset($options['fragment'])) {
      if (($fragment = trim($options['fragment'])) != '') {
        $fragment = '#' . $fragment;
      }
    }

    // Generate a relative URL having no path, just query string and fragment.
    if ($route->getOption('_no_path')) {
      $query = $options['query'] ? '?' . UrlHelper::buildQuery($options['query']) : '';
      $url = $query . $fragment;
      return $collect_bubbleable_metadata ? $generated_url->setGeneratedUrl($url) : $url;
    }

    $options += $route->getOption('default_url_options') ?: [];
    $options += ['prefix' => '', 'path_processing' => TRUE];

    $name = $this->getRouteDebugMessage($name);
    $this->processRoute($name, $route, $parameters, $generated_url);
    $path = $this->getInternalPathFromRoute($name, $route, $parameters, $options['query']);

    // Outbound path processors might need the route object for the path, e.g.
    // to get the path pattern.
    $options['route'] = $route;
    if ($options['path_processing']) {
      $path = $this->processPath($path, $options, $generated_url);
    }

    // Ensure the resulting path has at most one leading slash, to prevent it
    // becoming an external URL without a protocol like //example.com.
    if (strpos($path, '//') === 0) {
      $path = '/' . ltrim($path, '/');
    }
    // The contexts base URL is already encoded
    // (see Symfony\Component\HttpFoundation\Request).
    $path = str_replace($this->decodedChars[0], $this->decodedChars[1], rawurlencode($path));

    // Drupal paths rarely include dots, so skip this processing if possible.
    if (strpos($path, '/.') !== FALSE) {
      // Path segments "." and ".." are interpreted as relative reference when
      // resolving a URI; see http://tools.ietf.org/html/rfc3986#section-3.3
      // so we need to encode them as they are not used for this purpose here
      // otherwise we would generate a URI that, when followed by a user agent
      // (e.g. browser), does not match this route.
      $path = strtr($path, ['/../' => '/%2E%2E/', '/./' => '/%2E/']);
      if ('/..' === substr($path, -3)) {
        $path = substr($path, 0, -2) . '%2E%2E';
      }
      elseif ('/.' === substr($path, -2)) {
        $path = substr($path, 0, -1) . '%2E';
      }
    }

    if (!empty($options['prefix'])) {
      $path = ltrim($path, '/');
      $prefix = empty($path) ? rtrim($options['prefix'], '/') : $options['prefix'];
      $path = '/' . str_replace('%2F', '/', rawurlencode($prefix)) . $path;
    }

    $query = $options['query'] ? '?' . UrlHelper::buildQuery($options['query']) : '';

    // The base_url might be rewritten from the language rewrite in domain mode.
    if (isset($options['base_url'])) {
      $base_url = $options['base_url'];

      if (isset($options['https'])) {
        if ($options['https'] === TRUE) {
          $base_url = str_replace('http://', 'https://', $base_url);
        }
        elseif ($options['https'] === FALSE) {
          $base_url = str_replace('https://', 'http://', $base_url);
        }
      }

      $url = $base_url . $path . $query . $fragment;
      return $collect_bubbleable_metadata ? $generated_url->setGeneratedUrl($url) : $url;
    }

    $base_url = $this->context->getBaseUrl();

    $absolute = !empty($options['absolute']);
    if (!$absolute || !$host = $this->context->getHost()) {
      $url = $base_url . $path . $query . $fragment;
      return $collect_bubbleable_metadata ? $generated_url->setGeneratedUrl($url) : $url;
    }

    // Prepare an absolute URL by getting the correct scheme, host and port from
    // the request context.
    if (isset($options['https'])) {
      $scheme = $options['https'] ? 'https' : 'http';
    }
    else {
      $scheme = $this->context->getScheme();
    }
    $scheme_req = $route->getSchemes();
    if ($scheme_req && ($req = $scheme_req[0]) && $scheme !== $req) {
      $scheme = $req;
    }
    $port = '';
    if ('http' === $scheme && 80 != $this->context->getHttpPort()) {
      $port = ':' . $this->context->getHttpPort();
    }
    elseif ('https' === $scheme && 443 != $this->context->getHttpsPort()) {
      $port = ':' . $this->context->getHttpsPort();
    }
    if ($collect_bubbleable_metadata) {
      $generated_url->addCacheContexts(['url.site']);
    }
    $url = $scheme . '://' . $host . $port . $base_url . $path . $query . $fragment;
    return $collect_bubbleable_metadata ? $generated_url->setGeneratedUrl($url) : $url;
  }

}
