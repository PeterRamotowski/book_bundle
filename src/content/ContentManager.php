<?php

namespace Drupal\book_bundle\content;

use Drupal\book_bundle\content\ContentLoader;
use Drupal\book_bundle\data\BookData;
use Drupal\book_bundle\data\ChaptersData;
use Drupal\book_bundle\Entity\Book;
use Drupal\book_bundle\Entity\BookInterface;
use Drupal\book_bundle\Entity\Chapter;
use Drupal\book_bundle\Entity\ChapterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContentManager implements ContainerInjectionInterface
{

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new ContentManager object
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $currentUser)
  {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * @return EntityStorageInterface 
   */
  public function getBookStorage(): EntityStorageInterface
  {
    return $this->entityTypeManager->getStorage('book');
  }

  /**
   * @return EntityStorageInterface 
   */
  public function getChapterStorage(): EntityStorageInterface
  {
    return $this->entityTypeManager->getStorage('chapter');
  }

  /**
   * @param int $booksQuantity
   * @param int $chaptersQuantity
   */
  public function createContent(
    int $booksQuantity = BookInterface::DEFAULT_QUANTITY,
    int $chaptersQuantity = ChapterInterface::DEFAULT_QUANTITY
  ) {
    $userId = $this->currentUser->id();

    foreach (range(1, $booksQuantity) as $bookNr) {
      $book = $this->getBookStorage()->create([
        'type' => 'book',
        'title' => sprintf('Book %d', $bookNr),
        'uid' => $userId,
      ]);
      $book->save();

      foreach (range(1, $chaptersQuantity) as $chapterNr) {
        $chapter = $this->getChapterStorage()->create([
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
  protected function referenceChapters()
  {
    $chapters = $this->getChapterStorage()->loadMultiple();

    if (!count($chapters)) {
      return;
    }

    /** @var Chapter $chapter */
    foreach ($chapters as $chapter) {
      $bookData = new BookData($chapter->getBook());

      /** @var Book $book */
      $book = $this->getBookStorage()->load($bookData->getBookId());

      $chaptersData = new ChaptersData($book->getChapters());

      if (!in_array($chapter->id(), $chaptersData->getChaptersIds())) {
        $book->addChapter($chapter->id());
      }
    }
  }

  public function deleteAllContent()
  {
    $this->deleteContent($this->getBookStorage());
    $this->deleteContent($this->getChapterStorage());
  }

  /**
   * @param int $userId 
   */
  public function deleteAllUserContent(int $userId)
  {
    $this->deleteUserContent($this->getBookStorage(), $userId);
    $this->deleteUserContent($this->getChapterStorage(), $userId);
  }

  /**
   * @param EntityStorageInterface $storage
   * @param int $userId 
   */
  protected function deleteUserContent(EntityStorageInterface $storage, int $userId)
  {
    $contentLoader = new ContentLoader($storage);
    $entityIds = $contentLoader->getUserContent($userId);

    $this->deleteContent($storage, $entityIds);
  }

  /**
   * @param EntityStorageInterface $storage
   * @param null|array $entityIds 
   */
  protected function deleteContent(EntityStorageInterface $storage, ?array $entityIds = NULL)
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
    $this->anonymizeUserContent($this->getBookStorage(), $userId);
    $this->anonymizeUserContent($this->getChapterStorage(), $userId);
  }

  /**
   * @param EntityStorageInterface $storage
   * @param int $userId 
   */
  protected function anonymizeUserContent(EntityStorageInterface $storage, int $userId)
  {
    $contentLoader = new ContentLoader($storage);
    $entityIds = $contentLoader->getUserContent($userId);

    $this->anonymizeContent($storage, $entityIds);
  }

  /**
   * @param EntityStorageInterface $storage
   * @param array $entityIds
   */
  protected function anonymizeContent(EntityStorageInterface $storage, array $entityIds)
  {
    /** @var ContentEntityBase $entity */
    foreach ($storage->loadMultiple($entityIds) as $entity) {
      $entity->set('uid', 0);
      $entity->save();
    }
  }
}
