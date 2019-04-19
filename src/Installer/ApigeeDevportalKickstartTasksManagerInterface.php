<?php

/**
 * @file
 * Copyright 2019 Google Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 2 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public
 * License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Drupal\apigee_devportal_kickstart\Installer;

/**
 * Defines an interface for the Apigee kickstart task manager.
 */
interface ApigeeDevportalKickstartTasksManagerInterface {

  /**
   * This is a dummy batch operation to show visual feedback to the user.
   *
   * @param array $config
   *   An array of config.
   * @param array $context
   *   The batch context.
   */
  public static function init(array $config, array &$context);

  /**
   * Installs the given array of modules. Used as a batch operations.
   *
   * @param array $modules
   *   An array of module names.
   * @param array $context
   *   The batch context.
   */
  public static function installModules(array $modules, array &$context);

  /**
   * Imports an array of currencies.
   *
   * @param \Apigee\Edge\Api\Monetization\Entity\SupportedCurrencyInterface[] $currencies
   *   An array of supported currencies.
   * @param array $context
   *   The batch context.
   */
  public static function importCurrencies(array $currencies, array &$context);

  /**
   * Creates a commerce store.
   *
   * @param array $values
   *   An array of values for the store.
   * @param array $context
   *   The batch context.
   */
  public static function createStore(array $values, array &$context);

  /**
   * Creates a payment gateway.
   *
   * @param array $values
   *   An array of values for the gateway.
   * @param array $context
   *   The batch context.
   */
  public static function createPaymentGateway(array $values, array &$context);

  /**
   * Creates commerce products for each supported currencies.
   *
   * @param \Apigee\Edge\Api\Monetization\Entity\SupportedCurrencyInterface[] $currencies
   *   An array of supported currencies.
   * @param array $context
   *   The batch context.
   */
  public static function createProducts(array $currencies, array &$context);

}
