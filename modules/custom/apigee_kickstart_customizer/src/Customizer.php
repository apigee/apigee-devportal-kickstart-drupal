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

namespace Drupal\apigee_kickstart_customizer;

use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Defines the 'apigee_kickstart_customizer' service.
 */
class Customizer extends DefaultPluginManager implements CustomizerInterface {

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The active theme.
   *
   * @var \Drupal\Core\Theme\ActiveTheme
   */
  protected $activeTheme;

  /**
   * CustomizerThemeHandler constructor.
   *
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(ThemeManagerInterface $theme_manager, ThemeHandlerInterface $theme_handler) {
    $this->themeManager = $theme_manager;
    $this->themeHandler = $theme_handler;
    $this->activeTheme = $this->themeManager->getActiveTheme();
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    return new YamlDiscovery('customizer', $this->themeHandler->getThemeDirectories());
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    return $this->getDiscovery()->findAll();
  }

  /**
   * {@inheritdoc}
   */
  protected function providerExists($provider) {
    return $this->themeHandler->themeExists($provider);
  }

  /**
   * {@inheritdoc}
   */
  public function listAll(): array {
    return array_filter($this->themeHandler->listInfo(), function (Extension $theme) {
      return $this->isCustomizable($theme->getName());
    });
  }

  /**
   * {@inheritdoc}
   */
  public function isCustomizable($theme = NULL): bool {
    return count($this->getDefinitionForTheme($theme));
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitionForTheme($theme = NULL): array {
    $theme = $theme ?? $this->activeTheme->getName();

    $definitions = $this->getDefinitions();
    return isset($definitions[$theme]) ? $definitions[$theme] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig($theme = NULL): array {
    $theme = $theme ?? $this->activeTheme->getName();

    // Load theme.
    if (!($theme = $this->themeHandler->getTheme($theme))
      || !($definitions = $this->getDefinitionForTheme($theme->getName()))) {
      return [];
    }

    return array_diff_key($definitions, array_flip($this->reservedKeys()));
  }

  /**
   * {@inheritdoc}
   */
  public function reservedKeys(): array {
    return [
      'stylesheet',
    ];
  }

}
