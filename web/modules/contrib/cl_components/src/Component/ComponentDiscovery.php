<?php

namespace Drupal\cl_components\Component;

use Drupal\cl_components\Exception\ComponentNotFoundException;
use Drupal\cl_components\Exception\InvalidComponentException;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Discovers components.
 */
class ComponentDiscovery {

  use DependencySerializationTrait;

  /**
   * Directory iterator flags.
   *
   * @var int
   */
  private static $directoryIteratorFlags = \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_SELF | \FilesystemIterator::SKIP_DOTS;

  /**
   * Cached component information keyed by component ID.
   *
   * @var \Drupal\cl_components\Component\Component[]|null
   */
  private array $components;

  /**
   * Directories to scan for components.
   *
   * @var string[]
   */
  private array $scanDirs = [];

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private CacheBackendInterface $cache;

  /**
   * @var bool
   */
  private bool $debugMode;

  /**
   * Creates a new ComponentDiscovery.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CacheBackendInterface $cache) {
    $settings = $config_factory->get('cl_components.settings');
    $this->scanDirs = $settings->get('paths');
    $this->debugMode = (bool) $settings->get('debug');
    $this->cache = $cache;
  }

  /**
   * Finds all the components that represent organisms.
   *
   * @param bool $no_wip
   *   If TRUE, only return Components that are not marked as WIP.
   *
   * @return \Drupal\cl_components\Component\Component[]
   *   The modules.
   */
  public function findAllOrganisms(bool $no_wip = FALSE): array {
    $components = $this->findAll();
    $filtered = array_filter(
      $components,
      fn(Component $component) => $component->getMetadata()
          ->getComponentType() === ComponentMetadata::COMPONENT_TYPE_ORGANISM
    );
    if (!$no_wip) {
      return $filtered;
    }
    return array_filter(
      $filtered,
      fn(Component $component) => $component->getMetadata()
          ->getStatus() !== ComponentMetadata::COMPONENT_STATUS_WIP
    );
  }

  /**
   * Returns all the components in the repository.
   *
   * @returns \Drupal\cl_components\Component\Component[]
   *   The components found.
   */
  public function findAll(): array {
    if (isset($this->components)) {
      return array_values($this->components);
    }
    $components_by_key = fn(array $data) => array_reduce(
      $data,
      static fn(array $all, Component $c) => [...$all, $c->getId() => $c],
      []
    );
    $cache_ids = $this->cache->get('all-cache-ids');
    if ($cache_ids && is_array($cache_ids->data)) {
      $cache_entries = $this->cache->getMultiple($cache_ids->data);
      // Get the data of the cache properties.
      $cache_data = array_map(
        fn(object $item) => $item->data,
        $cache_entries
      );
      // Ensure cache data deserializes to a Component.
      $cached_components = array_filter(
        $cache_data,
        static fn(mixed $item) => $item instanceof Component
      );
      $this->components = $components_by_key($cached_components);
      return array_values($this->components);
    }
    $paths = $this->findAllPaths();
    $components = array_map([$this, 'createComponent'], $paths);
    $components = array_filter($components);
    $this->components = $components_by_key($components);
    $this->cache->set(
      'all-cache-ids',
      array_map(
        static fn(Component $component) => 'component::' . $component->getId(),
        $this->components
      )
    );
    $this->cache->setMultiple(array_reduce(
      $components,
      static fn(array $items, Component $component) => array_merge(
        $items,
        ['component::' . $component->getId() => ['data' => $component]]
      ),
      []
    ));
    return array_values($this->components);
  }

  /**
   * Finds the paths to all the components under the scan dirs.
   *
   * Note that this method is not cached. Use responsibly.
   *
   * @return string[]
   *   The paths to the component folders.
   */
  public function findAllPaths(): array {
    $unflattened = array_map(function (string $path) {
      try {
        $directory_iterator = new \RecursiveDirectoryIterator($path, static::$directoryIteratorFlags);
        return $this->discoverComponentPaths($directory_iterator, []);
      }
      catch (\UnexpectedValueException $exception) {
        watchdog_exception('cl_components', $exception);
        return [];
      }
    }, $this->scanDirs);
    return array_merge(...$unflattened);
  }

  /**
   * Returns all the components in the repository pointed by the iterator.
   *
   * @param \RecursiveDirectoryIterator $it
   *   The directory iterator for the component repository.
   * @param array $paths
   *   Internal variable for recursion handling.
   *
   * @return string[]
   *   The paths to the components that were found.
   *
   * @see https://fractal.build/guide/components/#what-defines-a-component
   */
  private function discoverComponentPaths(\RecursiveDirectoryIterator $it, array $paths): array {
    // If this is a folder, keep drilling down.
    if ($it->isDir() && $it->hasChildren()) {
      $children = $it->getChildren();
      assert($children instanceof \RecursiveDirectoryIterator);
      $paths = $this->discoverComponentPaths($children, $paths);
    }
    if (
      $it->getFilename() === 'metadata.json'
      // Exclude components that start with a _.
      && $it->getPath()[0] !== '_'
    ) {
      $paths[] = $it->getPath();
    }
    $it->next();
    $current = $it->current();
    return $it->valid() ? $this->discoverComponentPaths($current, array_unique($paths)) : $paths;
  }

  /**
   * Creates the library declaration array from a component.
   *
   * @param \Drupal\cl_components\Component\Component $component
   *   The component info.
   *
   * @return array
   *   The library for the Library API.
   */
  public function libraryFromComponent(Component $component): array {
    $library = [];
    $styles = $component->getStyles();
    if (!empty($styles)) {
      $library['css'] = [
        'component' => array_reduce($styles, function (array $css, string $file) {
          return array_merge($css, [$file => []]);
        }, []),
      ];
    }
    $scripts = $component->getScripts();
    if (!empty($scripts)) {
      $library['js'] = array_reduce($scripts, function (array $js, string $file) {
        return array_merge($js, [$file => []]);
      }, []);
    }

    $library['dependencies'] = array_merge(
    // Ensure that 'core/drupal' is always present.
      ['core/drupal'],
      $component->getMetadata()->getLibraryDependencies()
    );
    return $library;
  }

  /**
   * Finds a component by passing a loosely related children.
   *
   * This is particularly useful since storybook sends the path of the story
   * from the root of the repo regardless of where the drupal project is.
   *
   * @param string $filename
   *   File name.
   *
   * @return Component|null
   *   The component.
   *
   * @throws \Drupal\cl_components\Exception\ComponentNotFoundException
   */
  public function findBySiblingFile(string $filename): ?Component {
    // The file may be relative to something else.
    $regexp = '@^(\.\.?/)+@';
    $filename = preg_replace($regexp, '', $filename);
    $filename = preg_replace('@' . DRUPAL_ROOT . '@', '', $filename);
    // See if the file exists.
    $filename = $this->findPartialFile($filename);
    if (!$filename) {
      return NULL;
    }
    // Now let's see if the metadata file exists.
    $basename = dirname($filename);
    $metadata_file = $basename . DIRECTORY_SEPARATOR . 'metadata.json';
    if (!file_exists($metadata_file)) {
      return NULL;
    }
    $metadata = Json::decode(file_get_contents($metadata_file));
    $machine_name = $metadata['machineName'] ?? NULL;
    if (!$machine_name) {
      return NULL;
    }
    return $this->find($machine_name);
  }

  /**
   * Finds a partial filename.
   *
   * Used for intermediate steps when finding files.
   *
   * @param string $filename
   *   The filename.
   *
   * @return string|null
   *   The partial.
   */
  private function findPartialFile(string $filename): ?string {
    if (file_exists(DRUPAL_ROOT . DIRECTORY_SEPARATOR . $filename)) {
      return $filename;
    }
    $new_filename = preg_replace('@^[^/]*/@', '', $filename);
    if ($new_filename === $filename) {
      return NULL;
    }
    return $this->findPartialFile($new_filename);
  }

  /**
   * Finds a component by its ID.
   *
   * @param string $id
   *   The ID of the component to find.
   *
   * @return \Drupal\cl_components\Component\Component
   *   The component.
   *
   * @throws \Drupal\cl_components\Exception\ComponentNotFoundException
   *   When the component cannot be found.
   */
  public function find(string $id): Component {
    // Check if the component is in memory cache.
    if (($this->components[$id] ?? NULL) instanceof Component) {
      return $this->components[$id];
    }
    // Check if the component is in persisted cache.
    $cached = $this->cache->get('component::' . $id);
    if ($cached && $cached->data instanceof Component) {
      return $cached->data;
    }
    // Find all components and search for ours.
    $components = $this->findAll();
    $matches = array_filter(
      $components,
      static fn(Component $c) => $c->getId() === $id
    );
    $component = reset($matches);
    if ($component instanceof Component) {
      return $component;
    }
    $message = sprintf('Unable to find component "%s" in the component repository', $id);
    throw new ComponentNotFoundException($message);
  }

  /**
   * Gets the directories to scan for components.
   *
   * @return string[]
   *   The directories.
   */
  public function getScanDirs() {
    return $this->scanDirs;
  }

  /**
   * Creates a component from a component path.
   *
   * @param string $path
   *   The path to the directory that holds the component.
   *
   * @return \Drupal\cl_components\Component\Component|null The component.
   *   The component.
   */
  private function createComponent(string $path): ?Component {
    try {
      return $this->instantiateComponent($path);
    }
    catch (InvalidComponentException $e) {
      watchdog_exception('cl_components', $e, 'Invalid component @path. Error: @error', [
        '@path' => $path,
        '@error' => $e->getMessage(),
      ]);
      return NULL;
    }
  }

  /**
   * Creates a component from a component path.
   *
   * @param string $path
   *   The path to the directory that holds the component.
   *
   * @return \Drupal\cl_components\Component\Component
   *   The component.
   *
   * @throws \Drupal\cl_components\Exception\InvalidComponentException
   */
  public function instantiateComponent(string $path): Component {
    $assets = $this->discoverDistAssets($path);
    $templates = $this->discoverTemplates($path);
    $meta = $this->discoverMeta($path);
    $id = $meta->getMachineName();
    return new Component(
      $id,
      $templates,
      $assets['css'],
      $assets['js'],
      $meta,
      $this->debugMode
    );
  }

  /**
   * Given a component path discover all the CSS and JS assets to include.
   *
   * @param string $path
   *   The component path.
   *
   * @return array
   *   The list of assets in the "dist" directory to include for the component.
   */
  public function discoverDistAssets(string $path): array {
    $extensions = ['css', 'js'];
    $app_root = \Drupal::getContainer()->getParameter('app.root');
    $dirname = substr(dirname(__DIR__, 2), strlen($app_root) + 1);
    $num = count(explode(DIRECTORY_SEPARATOR, $dirname));
    // CL Components is the module owning the library definition. However, the
    // actual files live wherever the component is. We need to calculate the
    // relative route to the files starting from CL Component, since drupal
    // assumes paths for files in libraries are relative to the module owning
    // the library.
    $dots = implode(DIRECTORY_SEPARATOR, array_fill(0, $num, '..'));
    return array_reduce($extensions, function (array $carry, string $extension) use ($dots, $path) {
      $full_path = sprintf('%s/%s', $path, $extension);
      $prefix = sprintf('%s/%s/%s', $dots, $path, $extension);
      $files = [];
      if (file_exists($full_path) && is_dir($full_path)) {
        try {
          $it = new \RecursiveDirectoryIterator($full_path, static::$directoryIteratorFlags);
          $files = array_map(
            fn(string $file) => sprintf('%s/%s', $prefix, $file),
            $this->findSubpathByExtension($it, $extension)
          );
        }
        catch (\UnexpectedValueException $exception) {
        }
      }
      return array_merge($carry, [$extension => $files]);
    }, []);
  }

  /**
   * Helper function to find assets by file extension in the provided directory.
   *
   * @param \DirectoryIterator $it
   *   The directory iterator to find files on.
   * @param string $extension
   *   The extension of the files.
   *
   * @return array
   *   The list of subpaths with all the files of the given extension.
   */
  private function findSubpathByExtension(\DirectoryIterator $it, string $extension): array {
    $files = [];
    while ($it->valid()) {
      if ($it->getExtension() === $extension) {
        $files[] = $it->getSubPathname();
      }
      $it->next();
    }
    return $files;
  }

  /**
   * Given a component path discover all the twig templates to include.
   *
   * @param string $path
   *   The component path.
   *
   * @return string[]
   *   The list of templates to include for the component.
   */
  private function discoverTemplates(string $path): array {
    $files = [];
    try {
      $it = new \RecursiveDirectoryIterator($path, static::$directoryIteratorFlags);
      $files = $this->findSubpathByExtension($it, 'twig');
    }
    catch (\UnexpectedValueException $exception) {
    }
    // Ensure the templates DO NOT end in '.html.twig'.
    return array_filter($files, function (string $filename) {
      $extension = '.html.twig';
      $pos = strpos($filename, $extension);
      if ($pos === FALSE) {
        return TRUE;
      }
      return $pos !== strlen($filename) - strlen($extension);
    });
  }

  /**
   * Given a component path discover all the variant information.
   *
   * @param string $path
   *   The component path.
   *
   * @return ComponentMetadata
   *   The meta information keyed by 'variants'.
   */
  private function discoverMeta(string $path): ComponentMetadata {
    return new ComponentMetadata($path);
  }

}
