<?php

namespace Drupal\book_bundle\data;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;

class BookData
{

  /**
   * @var EntityReferenceFieldItemListInterface
   */
  protected $book;

  /**
   * @param EntityReferenceFieldItemListInterface $book 
   */
  public function __construct(EntityReferenceFieldItemListInterface $book)
  {
    $this->book = $book;
  }

  public function getBookId(): string
  {
    return $this->book->getString();
  }

  public function getBookTitle(): string
  {
    return $this->book->entity->getTitle();
  }
}
