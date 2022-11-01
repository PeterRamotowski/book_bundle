<?php

namespace Drupal\book_bundle\Form;

use Drupal\book_bundle\content\ContentManager;
use Drupal\book_bundle\Entity\BookInterface;
use Drupal\book_bundle\Entity\ChapterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BookBundleSettingsForm extends FormBase
{

  /**
   * @var ContentManager
   */
  protected $contentManager;

  public function __construct(ContentManager $contentManager)
  {
    $this->contentManager = $contentManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('book_bundle.content_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'book_bundle_clear_content';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $books = $this->contentManager->getBookStorage()->loadMultiple();
    $chapters = $this->contentManager->getChapterStorage()->loadMultiple();

    $booksQuantity = count($books);
    $chaptersQuantity = count($chapters);

    $form['group_creating'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Create Book and Chapter content'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );

    $form['group_creating']['books_quantity'] = [
      '#type' => 'number',
      '#default_value' => BookInterface::DEFAULT_QUANTITY,
      '#min' => '1',
      '#title' => $this->t('How many books to generate?'),
      '#required' => TRUE,
    ];

    $form['group_creating']['chapters_quantity'] = [
      '#type' => 'number',
      '#default_value' => ChapterInterface::DEFAULT_QUANTITY,
      '#min' => '1',
      '#title' => $this->t('How many chapters to generate?'),
      '#description' => $this->t('for each book'),
      '#required' => TRUE,
    ];

    $form['group_creating']['actions'] = [
      '#type' => 'actions',
    ];
    $form['group_creating']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create'),
      '#submit' => array([$this, 'submitCreatingForm']),
      '#button_type' => 'primary',
    ];

    $form['group_removing'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Remove @books books and @chapters chapters', array('@books' => $booksQuantity, '@chapters' => $chaptersQuantity)),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );

    $form['group_removing']['actions'] = [
      '#type' => 'actions',
    ];
    $form['group_removing']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove'),
      '#submit' => array([$this, 'submitRemovingForm']),
      '#button_type' => 'danger',
    ];

    // disable creating when content exists
    if ($booksQuantity || $chaptersQuantity) {
      $form['group_creating']['#disabled'] = TRUE;
    }

    // hide removing when content not exists
    if (!$booksQuantity && !$chaptersQuantity) {
      unset($form['group_removing']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitCreatingForm(array &$form, FormStateInterface $form_state)
  {
    $this->contentManager->createContent(
      $form_state->getValue('books_quantity'),
      $form_state->getValue('chapters_quantity')
    );

    $this->messenger()->addStatus($this->t('Book\'s and Chapter\'s content has been created.'));
  }

  /**
   * {@inheritdoc}
   */
  public function submitRemovingForm()
  {
    $this->contentManager->deleteAllContent();

    $this->messenger()->addStatus($this->t('All Book\'s and Chapter\'s content has been removed.'));
  }
}
