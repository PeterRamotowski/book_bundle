<?php

namespace Drupal\book_bundle\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

class BookForm extends ContentEntityForm
{

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state)
  {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New book %label has been created.', $message_arguments));
        $this->logger('book_bundle')->notice('Created new book %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The book %label has been updated.', $message_arguments));
        $this->logger('book_bundle')->notice('Updated book %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.book.canonical', ['book' => $entity->id()]);

    return $result;
  }
}
