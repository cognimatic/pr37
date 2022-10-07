<?php

namespace Drupal\media_link_enhancements\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface;
use Drupal\media_link_enhancements\Entity\MediaLinkEnhancementsMedia;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Overides the core media controller to provide viewing enhancements.
 */
class MediaLinkEnhancementsController extends EntityViewController {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The entity type manager.
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
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs an MBRLinksController instance.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\media_link_enhancements\MediaLinkEnhancementsHelperInterface $helper
   *   Helper service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(AccountInterface $current_user, CurrentPathStack $current_path, RequestStack $requestStack, AliasManagerInterface $alias_manager, LoggerChannelFactoryInterface $logger_factory, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, MediaLinkEnhancementsHelperInterface $helper, EntityRepositoryInterface $entity_repository) {
    $this->currentUser = $current_user;
    $this->currentPath = $current_path;
    $this->requestStack = $requestStack;
    $this->aliasManager = $alias_manager;
    $this->loggerFactory = $logger_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory->get('media_link_enhancements.settings');
    $this->helper = $helper;
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('path.current'),
      $container->get('request_stack'),
      $container->get('path_alias.manager'),
      $container->get('logger.factory'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('media_link_enhancements.helper'),
      $container->get('entity.repository'),
    );
  }

  /**
   * The _title_callback for the page that renders a single media entity.
   *
   * @param \Drupal\media_link_enhancements\Entity\MediaLinkEnhancementsMedia $media
   *   The current media.
   *
   * @return string
   *   The media label.
   */
  public function title(MediaLinkEnhancementsMedia $media) {
    return $this->entityRepository->getTranslationFromContext($media)->label();
  }

  /**
   * {@inheritdoc}
   */
// @codingStandardsIgnoreStart
/*
  public function buildTitle(array $media) {
    return parent::buildTitle($media);
  }
*/
// @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function download() {

    $current_path = $this->currentPath->getPath();
    $alias = $this->aliasManager->getPathByAlias($current_path);
    $params = Url::fromUri('internal:' . $alias)->getRouteParameters();
    $entity_type = key($params);
    $media_id = $params[$entity_type];

    // Provides a shortcut to the media edit page. This is especially
    // useful when using path aliases for media. Just append "?edit-media"
    // onto the URL for the media's alias and you will be redirected to
    // the media's edit page.
    $path = $this->requestStack->getCurrentRequest()->getRequestUri();
    if (strpos($path, 'edit-media') !== FALSE && !$this->currentUser->isAnonymous()) {
      return new RedirectResponse('/media/' . $media_id . '/edit');
    }

    $media = $this->entityTypeManager->getStorage('media')->load($media_id);

    if (!$media || !$media instanceof Media) {
      $this->loggerFactory->get('media_link_enhancements')->notice("Can't find media object for " . $current_path);
      throw new NotFoundHttpException("Can't find media object.");
    }

    $url = FALSE;
    $file = FALSE;
    $source = $media->getSource()->getSourceFieldValue($media);

    if (strpos($source, 'http') !== FALSE) {
      $url = $source;
    }
    elseif (is_numeric($source)) {
      $file = $this->entityTypeManager->getStorage('file')->load($source);
      if ($file && $file instanceof File) {
        $url = str_replace('public://', '', $file->getFileUri());
        $url = '/' . PublicStream::basePath() . '/' . $url;
      }
    }

    $redirectEnabled = !empty($this->configFactory->get('enable_redirect'));
    $binaryResponseEnabled = !empty($this->configFactory->get('enable_binary_response'));

    // Handle redirects.
    if ($redirectEnabled) {

      if ($url && $this->helper->checkBundle($media->bundle(), 'redirect_bundles')) {
        $redirectUrl = TRUE;
        if ($file) {
          $extension = pathinfo($file->getFileName(), PATHINFO_EXTENSION);
          if (!$this->helper->checkExtension($extension, 'redirect_extensions')) {
            $redirectUrl = FALSE;
          }
        }

        if ($redirectUrl) {
          $response = new TrustedRedirectResponse($url, 303);
          return $response;
        }
      }
    }

    // Handle binary responses.
    if ($binaryResponseEnabled) {

      // Throw error is the file entity isn't available.
      if (!$file || !$file instanceof File) {
        $this->loggerFactory->get('media_link_enhancements')
          ->notice("File id could not be loaded for " . $current_path);
        throw new \Exception("File id could not be loaded for {$current_path}.");
      }

      $filename = $file->getFilename();
      $extension = pathinfo($filename, PATHINFO_EXTENSION);
      if (
        $this->helper->checkBundle($media->bundle(), 'binary_response_bundles')
          &&
        $this->helper->checkExtension($extension, 'binary_response_extensions')
      ) {
        $uri = $file->getFileUri();

        // Throw error if file does not exist on disk.
        if (!file_exists($uri)) {
          $this->loggerFactory->get('media_link_enhancements')
            ->notice("File does not exist for " . $current_path);
          throw new NotFoundHttpException("The file {$uri} does not exist.");
        }

        $response = new BinaryFileResponse($uri);
        $response->setContentDisposition(
          ResponseHeaderBag::DISPOSITION_INLINE,
          $filename
        );

        return $response;
      }
    }

    // Default media display.
    $page = $this->entityTypeManager
      ->getViewBuilder($media->getEntityTypeId())
      ->view($media, 'full');

    $page['#entity_type'] = $media->getEntityTypeId();
    $page['#' . $page['#entity_type']] = $media;

    return $page;
  }

}
