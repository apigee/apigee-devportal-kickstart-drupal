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

namespace Drupal\apigee_kickstart_customizer\Routing;

use Drupal\apigee_kickstart_customizer\CustomizerInterface;
use Drupal\apigee_kickstart_customizer\Form\ThemeCustomizerForm;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

class CustomizerRoutes implements ContainerInjectionInterface {

  /**
   * The customizer.
   *
   * @var \Drupal\apigee_kickstart_customizer\CustomizerInterface
   */
  protected $customizer;

  /**
   * CustomizerRoutes constructor.
   *
   * @param \Drupal\apigee_kickstart_customizer\CustomizerInterface $customizer
   *   The customizer.
   */
  public function __construct(CustomizerInterface $customizer) {
    $this->customizer = $customizer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('apigee_kickstart_customizer')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $routes = [];
    foreach ($this->customizer->listAll() as $theme => $theme_info) {
      $routes["apigee_kickstart_customizer.customizer_theme_$theme"] = new Route(
        '/admin/appearance/customizer/{theme}',
        [
          '_form' => ThemeCustomizerForm::class,
          '_title' => $theme_info->info['name'],
        ],
        [
          '_permission' => 'administer themes',
        ]
      );
    }
    return $routes;
  }

}
