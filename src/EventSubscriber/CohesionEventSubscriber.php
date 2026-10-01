<?php

namespace Drupal\cohesion\EventSubscriber;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Generic Site Studio event subscriber  .
 */
class CohesionEventSubscriber implements EventSubscriberInterface {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The CohesionUtils.
   *
   * @var \Drupal\cohesion\Services\CohesionUtils
   */
  protected $cohesionUtils;


  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs the event subscriber.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   *
   * @param \Drupal\cohesion\Services\CohesionUtils $cohesionUtils
   *   The CohesionUtils.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(MessengerInterface $messenger, CohesionUtils $cohesionUtils, ThemeHandlerInterface $theme_handler) {
    $this->messenger = $messenger;
    $this->cohesionUtils = $cohesionUtils;
    $this->themeHandler = $theme_handler;
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onConfigSave', 40];
    return $events;
  }

  /**
   * Reacts to a config save.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   */
  public function onConfigSave(ConfigCrudEvent $event) {
    $config = $event->getConfig();

    if ($config->getName() == 'system.theme' && $config->getOriginal('default') !== $config->get('default') && $this->cohesionUtils->themeHasCohesionEnabled($config->get('default')) && !theme_get_setting('features.cohesion_build_assets', $config->get('default'))) {
      $theme = $this->themeHandler->listInfo()[$config->get('default')];
      $this->messenger->addMessage(t(
        '%theme is using Site Studio and needs assets to be built for it. You may need to perform a <a href=":rebuild">rebuild</a>',
        [
          '%theme' => $theme->info['name'],
          ':rebuild' => Url::fromRoute('cohesion.configuration.rebuild')->toString(),
        ]),
        MessengerInterface::TYPE_WARNING);
    }

    if (!is_null($config->get('features.cohesion_build_assets')) && $config->get('features.cohesion_build_assets') == 1 && ($config->getOriginal('features.cohesion_build_assets') == 0 || is_null($config->getOriginal('features.cohesion_build_assets')))) {
      $this->messenger->addMessage(t('The theme is now using Site Studio and needs assets to be built for it. You may need to perform a <a href=":rebuild">rebuild</a>', [':rebuild' => Url::fromRoute('cohesion.configuration.rebuild')->toString()]), MessengerInterface::TYPE_WARNING);
    }

    if (!is_null($config->get('features.layout_canvas_field')) && $config->get('features.layout_canvas_field') == 1 && ($config->getOriginal('features.layout_canvas_field') == 0 || is_null($config->getOriginal('features.layout_canvas_field')))) {
      $this->messenger->addMessage(t('The theme is now using Site Studio and needs assets to be built for it. You may need to perform a <a href=":rebuild">rebuild</a>', [':rebuild' => Url::fromRoute('cohesion.configuration.rebuild')->toString()]), MessengerInterface::TYPE_WARNING);
    }
  }

}
