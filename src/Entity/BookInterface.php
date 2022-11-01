<?php

namespace Drupal\book_bundle\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a book entity type.
 */
interface BookInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
  const DEFAULT_QUANTITY = 3;

  public function getTitle(): string;

  public function getChapters(): FieldItemListInterface;
}
