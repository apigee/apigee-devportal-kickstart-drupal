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

/**
 * Defines an interface for the customizer service.
 */
interface CustomizerInterface {

  /**
   * Checks if the given theme is customizable.
   *
   * @param string $theme
   *   The name of the theme.
   *
   * @return bool
   *   TRUE is the given theme is customizable.
   */
  public function isCustomizable($theme = NULL): bool;

  /**
   * Returns an array of customizable themes.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   An array of theme extensions.
   */
  public function listAll(): array;

  /**
   * Returns the definition for a theme.
   *
   * @param string $theme
   *   The name of the theme.
   *
   * @return array
   *   An array of definitions.
   */
  public function getDefinitionForTheme($theme = NULL): array;

  /**
   * Updates the stylesheet for a theme.
   *
   * @param string $theme
   *   The name of the theme.
   */
  public function updateStylesheetForTheme($theme = NULL): void;

  /**
   * Deletes the stylesheet for a theme.
   *
   * @param string $theme
   *   The name of the theme.
   */
  public function deleteStylesheetForTheme($theme = NULL): void;

  /**
   * Returns customizer config for a theme.
   *
   * @param string $theme
   *   The name of the theme.
   *
   * @return array
   *   An array of config.
   */
  public function getConfig($theme = NULL): array;

  /**
   * Returns an array of keys that are not passed down to the customizer.
   *
   * @return array
   *   An array of reserved keys.
   */
  public function reservedKeys(): array;

}
