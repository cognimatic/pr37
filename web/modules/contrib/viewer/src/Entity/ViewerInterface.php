<?php

namespace Drupal\viewer\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Viewer entities.
 *
 * @ingroup viewer
 */
interface ViewerInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

}
