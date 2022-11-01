<?php

namespace Drupal\book_bundle\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a chapter entity type
 */
interface ChapterInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
  const DEFAULT_QUANTITY = 5;

  public function getTitle(): string;

  public function getBook(): EntityReferenceFieldItemListInterface;
}
