<?php

namespace Drupal\book_bundle\content;

use Drupal\Core\Entity\EntityStorageInterface;

class ContentLoader
{

  /**
   * @var EntityStorageInterface
   */
  protected $storage;

  /**
   * @param EntityStorageInterface $storage 
   */
  public function __construct(EntityStorageInterface $storage)
  {
    $this->storage = $storage;
  }

  /**
   * @param int $uid 
   * @return array 
   */
  public function getUserContent(int $uid): array
  {
    return $this->storage->getQuery()
      ->condition('uid', $uid)
      ->execute();
  }
}
