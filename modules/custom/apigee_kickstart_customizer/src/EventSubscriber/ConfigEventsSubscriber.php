<?php

/**
 * Copyright 2019 Google Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

namespace Drupal\apigee_kickstart_customizer\EventSubscriber;

use Drupal\apigee_kickstart_customizer\CustomizerInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * An event subscriber to re-create stylesheet when config is updated.
 */
class ConfigEventsSubscriber implements EventSubscriberInterface {

  /**
   * The customizer service.
   *
   * @var \Drupal\apigee_kickstart_customizer\CustomizerInterface
   */
  protected $customizer;

  /**
   * ConfigEventsSubscriber constructor.
   *
   * @param \Drupal\apigee_kickstart_customizer\CustomizerInterface $customizer
   *   The customizer service.
   */
  public function __construct(CustomizerInterface $customizer) {
    $this->customizer = $customizer;
  }

  /**
   * Re-creates stylesheet for theme when config is updated.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The config event.
   */
  public function onSave(ConfigCrudEvent $event) {
    if (preg_match('/^apigee_kickstart_customizer\.theme\.([^\.]*)$/', $event->getConfig()->getName(), $matches)) {
      $this->customizer->updateStylesheetForTheme($matches[1]);
    }
  }

  /**
   * Removes stylesheet for theme when config is deleted.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The config event.
   */
  public function onDelete(ConfigCrudEvent $event) {
    if (preg_match('/^apigee_kickstart_customizer\.theme\.([^\.]*)$/', $event->getConfig()->getName(), $matches)) {
      $this->customizer->deleteStylesheetForTheme($matches[1]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onSave'];
    $events[ConfigEvents::DELETE][] = ['onDelete'];

    return $events;
  }

}
