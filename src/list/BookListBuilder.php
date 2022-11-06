<?php

namespace Drupal\book_bundle\list;

use Drupal\book_bundle\data\ChaptersData;
use Drupal\book_bundle\Entity\Book;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for the book entity type
 */
class BookListBuilder extends EntityListBuilder
{

  /**
   * The date formatter service
   *
   * @var DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new BookListBuilder object
   *
   * @param EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param EntityStorageInterface $storage
   *   The entity storage class.
   * @param DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter)
  {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type)
  {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render()
  {
    $build['table'] = parent::render();

    $total = $this->getStorage()
      ->getQuery()
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    $build['summary']['#markup'] = $this->t('Total books: @total', ['@total' => $total]);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader()
  {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['chapters'] = $this->t('Chapters');
    $header['uid'] = $this->t('Author');
    $header['created'] = $this->t('Created');
    $header['changed'] = $this->t('Updated');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity)
  {
    /** @var Book $entity */

    $chaptersData = new ChaptersData($entity->getChapters());

    $row['id'] = $entity->id();
    $row['title'] = $entity->toLink();
    $row['chapters']['data'] = [
      '#theme' => 'chapters',
      '#chapters' => $chaptersData->getChaptersEntities(),
    ];
    $row['uid']['data'] = [
      '#theme' => 'username',
      '#account' => $entity->getOwner(),
    ];
    $row['created'] = $this->dateFormatter->format($entity->get('created')->value);
    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime());

    return $row + parent::buildRow($entity);
  }
}
