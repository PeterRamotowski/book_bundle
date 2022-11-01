<?php

namespace Drupal\book_bundle\content;

class ContentConfig
{

  public static function setupPathauto()
  {
    $config = \Drupal::service('config.factory')->getEditable('pathauto.settings');

    $enabledEntityTypes = $config->get('enabled_entity_types');
    $enabledEntityTypes = ['book', 'chapter', ...$enabledEntityTypes];

    $config->set('enabled_entity_types', array_unique($enabledEntityTypes))->save();
  }
}
