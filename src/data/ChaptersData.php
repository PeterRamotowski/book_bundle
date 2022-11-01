<?php

namespace Drupal\book_bundle\data;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;

class ChaptersData
{

  /**
   * @var EntityReferenceFieldItemListInterface
   */
  protected $chapters;

  /**
   * @param EntityReferenceFieldItemListInterface $chapters 
   */
  public function __construct(EntityReferenceFieldItemListInterface $chapters)
  {
    $this->chapters = $chapters;
  }

  public function getChaptersValue(): array
  {
    return $this->chapters->getValue();
  }

  public function getChaptersIds(): array
  {
    return array_column($this->getChaptersValue(), 'target_id');
  }

  public function getChaptersEntities(): array
  {
    return $this->chapters->referencedEntities();
  }
}
