<?php

namespace Drupal\book_bundle\content;

use Drupal\book_bundle\content\ContentLoader;
use Drupal\book_bundle\data\BookData;
use Drupal\book_bundle\data\ChaptersData;
use Drupal\book_bundle\Entity\Book;
use Drupal\book_bundle\Entity\BookInterface;
use Drupal\book_bundle\Entity\Chapter;
use Drupal\book_bundle\Entity\ChapterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\ContentEntityBase;

class ContentManager
{

  /**
   * @var EntityStorageInterface
   */
  protected $bookStorage;

  /**
   * @var EntityStorageInterface
   */
  protected $chapterStorage;

  /**
   * Constructs a new ContentManager object
   */
  public function __construct()
  {
    $this->bookStorage = \Drupal::entityTypeManager()->getStorage('book');
    $this->chapterStorage = \Drupal::entityTypeManager()->getStorage('chapter');
  }

  /**
   * @return EntityStorageInterface 
   */
  public function getBookStorage(): EntityStorageInterface
  {
    return $this->bookStorage;
  }

  /**
   * @return EntityStorageInterface 
   */
  public function getChapterStorage(): EntityStorageInterface
  {
    return $this->chapterStorage;
  }

  /**
   * @param int $booksQuantity
   * @param int $chaptersQuantity
   */
  public function createContent(
    int $booksQuantity = BookInterface::DEFAULT_QUANTITY,
    int $chaptersQuantity = ChapterInterface::DEFAULT_QUANTITY
  ) {
    $userId = \Drupal::currentUser()->id();

    foreach (range(1, $booksQuantity) as $bookNr) {
      $book = $this->bookStorage->create([
        'type' => 'book',
        'title' => sprintf('Book %d', $bookNr),
        'uid' => $userId,
      ]);
      $book->save();

      foreach (range(1, $chaptersQuantity) as $chapterNr) {
        $chapter = $this->chapterStorage->create([
          'type' => 'chapter',
          'title' => sprintf('Chapter %d', $chapterNr),
          'uid' => $userId,
          'book' => $book->id(),
        ]);
        $chapter->save();
      }
    }

    $this->referenceChapters();
  }

  /**
   * Reverse reference chapters in books
   */
  public function referenceChapters()
  {
    $chapters = $this->chapterStorage->loadMultiple();

    if (!count($chapters)) {
      return;
    }

    /** @var Chapter $chapter */
    foreach ($chapters as $chapter) {
      $bookData = new BookData($chapter->getBook());

      /** @var Book $book */
      $book = $this->bookStorage->load($bookData->getBookId());

      $chaptersData = new ChaptersData($book->getChapters());

      if (!in_array($chapter->id(), $chaptersData->getChaptersIds())) {
        $book->addChapter($chapter->id());
      }
    }
  }

  public function deleteAllContent()
  {
    $this->deleteContent($this->bookStorage);
    $this->deleteContent($this->chapterStorage);
  }

  /**
   * @param int $userId 
   */
  public function deleteAllUserContent(int $userId)
  {
    $this->deleteUserContent($this->bookStorage, $userId);
    $this->deleteUserContent($this->chapterStorage, $userId);
  }

  /**
   * @param EntityStorageInterface $storage
   * @param int $userId 
   */
  public function deleteUserContent(EntityStorageInterface $storage, int $userId)
  {
    $contentLoader = new ContentLoader($storage);
    $entityIds = $contentLoader->getUserContent($userId);

    $this->deleteContent($storage, $entityIds);
  }

  /**
   * @param EntityStorageInterface $storage
   * @param null|array $entityIds 
   */
  public function deleteContent(EntityStorageInterface $storage, ?array $entityIds = NULL)
  {
    /** @var ContentEntityBase $entity */
    foreach ($storage->loadMultiple($entityIds) as $entity) {
      $entity->delete();
    }
  }

  /**
   * @param int $userId 
   */
  public function anonymizeAllUserContent(int $userId)
  {
    $this->anonymizeUserContent($this->bookStorage, $userId);
    $this->anonymizeUserContent($this->chapterStorage, $userId);
  }

  /**
   * @param EntityStorageInterface $storage
   * @param int $userId 
   */
  public function anonymizeUserContent(EntityStorageInterface $storage, int $userId)
  {
    $contentLoader = new ContentLoader($storage);
    $entityIds = $contentLoader->getUserContent($userId);

    $this->anonymizeContent($storage, $entityIds);
  }

  /**
   * @param EntityStorageInterface $storage
   * @param array $entityIds
   */
  public function anonymizeContent(EntityStorageInterface $storage, array $entityIds)
  {
    /** @var ContentEntityBase $entity */
    foreach ($storage->loadMultiple($entityIds) as $entity) {
      $entity->set('uid', 0);
      $entity->save();
    }
  }
}
