<?php

/**
 * Copyright 2018 Google Inc.
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

namespace Drupal\apigee_kickstart_customizer\Plugin\Derivative;

use Drupal\apigee_kickstart_customizer\CustomizerInterface;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic tabs based on active themes.
 */
class ThemeLocalTask extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The theme customizer.
   *
   * @var \Drupal\apigee_kickstart_customizer\CustomizerInterface
   */
  protected $customizer;

  /**
   * Constructs a new ThemeLocalTask instance.
   *
   * @param \Drupal\apigee_kickstart_customizer\CustomizerInterface $customizer
   *  The theme customizer.
   */
  public function __construct(CustomizerInterface $customizer) {
    $this->customizer = $customizer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('apigee_kickstart_customizer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    \Drupal::logger('customize')->debug('asdss');
    foreach ($this->customizer->listAll() as $theme_name => $theme) {
      $this->derivatives[$theme_name] = $base_plugin_definition;
      $this->derivatives[$theme_name]['title'] = $theme->info['name'];
      $this->derivatives[$theme_name]['route_parameters'] = ['theme' => $theme_name];
    }
    return $this->derivatives;
  }

}
