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

use Drupal\Core\Asset\AssetCollectionOptimizerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
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
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The asset collection optimizer.
   *
   * @var \Drupal\Core\Asset\AssetCollectionOptimizerInterface
   */
  protected $collectionOptimizer;

  /**
   * CustomizerThemeHandler constructor.
   *
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Asset\AssetCollectionOptimizerInterface $collection_optimizer
   *   The asset collection optimizer.
   */
  public function __construct(ThemeManagerInterface $theme_manager, ThemeHandlerInterface $theme_handler, FileSystemInterface $file_system, ConfigFactoryInterface $config_factory, AssetCollectionOptimizerInterface $collection_optimizer) {
    $this->themeManager = $theme_manager;
    $this->themeHandler = $theme_handler;
    $this->activeTheme = $this->themeManager->getActiveTheme();
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
    $this->collectionOptimizer = $collection_optimizer;
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
    return $definitions[$theme] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function updateStylesheetForTheme($theme = NULL): void {
    $theme = $theme ?? $this->activeTheme->getName();
    $values = $this->configFactory->get('apigee_kickstart_customizer.theme.' . $theme)
      ->get('values');

    // TODO: Add scope support.
    $css = ':root {';
    foreach ($values as $name => $value) {
      if ($value) {
        $css .= "$name: $value;";
      }
    }
    $css .= '}';

    // Save value to file.
    $this->fileSystem->saveData($css, "public://apigee_kickstart_customizer.$theme.css", FileSystemInterface::EXISTS_REPLACE);

    // Rebuild caches.
    $this->collectionOptimizer->deleteAll();
    Cache::invalidateTags(['library_info']);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteStylesheetForTheme($theme = NULL): void {
    $theme = $theme ?? $this->activeTheme->getName();

    $this->fileSystem->delete("public://apigee_kickstart_customizer.$theme.css");

    // Rebuild caches.
    $this->collectionOptimizer->deleteAll();
    Cache::invalidateTags(['library_info']);
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
