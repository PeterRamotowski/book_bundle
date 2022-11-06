<?php

namespace Drupal\book_bundle\content;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContentConfig implements ContainerInjectionInterface
{

  /**
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ContentConfig object
   */
  public function __construct(ConfigFactoryInterface $configFactory)
  {
    $this->configFactory = $configFactory;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
    );
  }

  public function setupPathauto()
  {
    $config = $this->configFactory->getEditable('pathauto.settings');

    $enabledEntityTypes = $config->get('enabled_entity_types');
    $enabledEntityTypes = ['book', 'chapter', ...$enabledEntityTypes];

    $config->set('enabled_entity_types', array_unique($enabledEntityTypes))->save();
  }
}
