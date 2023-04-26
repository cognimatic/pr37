<?php

namespace Drupal\viewer\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining ViewerSource entities.
 *
 * @ingroup viewer
 */
interface ViewerSourceInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

}
