<?php

namespace Drupal\cohesion_style_guide\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Utility\Token;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Resets token info when style guides are saved or deleted.
 */
class StyleGuideTokenSubscriber implements EventSubscriberInterface {

  const CONFIG_PREFIX = 'cohesion_style_guide.cohesion_style_guide.';

  public function __construct(
    protected Token $token,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::SAVE => 'onConfigChange',
      ConfigEvents::DELETE => 'onConfigChange',
    ];
  }

  /**
   * Resets token info when a style guide config changes.
   */
  public function onConfigChange(ConfigCrudEvent $event): void {
    if (str_starts_with($event->getConfig()->getName(), self::CONFIG_PREFIX)) {
      $this->token->resetInfo();
    }
  }

}
