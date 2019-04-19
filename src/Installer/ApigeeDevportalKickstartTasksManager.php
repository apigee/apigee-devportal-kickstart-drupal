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

use Drupal\apigee_m10n_add_credit\AddCreditConfig;
use Drupal\commerce_price\CurrencyImporterInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\MissingDependencyException;
use Drupal\Core\Extension\ModuleInstallerInterface;

/**
 * Defines a service for performing additional tasks in batch.
 */
class ApigeeDevportalKickstartTasksManager implements ApigeeDevportalKickstartTasksManagerInterface {

//  /**
//   * The entity type manager.
//   *
//   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
//   */
//  protected $entityTypeManager;
//
//  /**
//   * The module installer.
//   *
//   * @var \Drupal\Core\Extension\ModuleInstallerInterface
//   */
//  protected $moduleInstaller;
//
//  /**
//   * The config factory.
//   *
//   * @var \Drupal\Core\Config\ConfigFactoryInterface
//   */
//  protected $configFactory;
//
//  /**
//   * ApigeeDevportalKickstartTasksManager constructor.
//   *
//   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
//   *   The entity type manager.
//   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
//   *   The module installer.
//   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
//   *   The config factory.
//   */
//  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleInstallerInterface $module_installer, ConfigFactoryInterface $config_factory) {
//    $this->entityTypeManager = $entity_type_manager;
//    $this->moduleInstaller = $module_installer;
//    $this->configFactory = $config_factory;
//  }

  /**
   * {@inheritdoc}
   */
  public static function installModules(array $modules, &$context) {
    try {
      \Drupal::service('module_installer')->install($modules);

      if (!isset($context['sandbox']['progress'])) {
        $context['sandbox']['progress'] = 0;
      }

      $context['sandbox']['progress']++;
      $context['message'] = t('Installed monetization modules.');
    } catch (\Exception $exception) {
      watchdog_exception('apigee_kickstart', $exception);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function importCurrencies(array $currencies, &$context) {
    foreach ($currencies as $currency) {
      // Import the currency.
      \Drupal::service('commerce_price.currency_importer')->import($currency->getName());
    }

    $context['sandbox']['progress']++;
    $context['message'] = t('Imported supported currencies.');
  }

  /**
   * {@inheritdoc}
   */
  public static function createStore(array $values, &$context) {
    try {
      $store = \Drupal::entityTypeManager()->getStorage('commerce_store')
        ->create($values);
      $store->save();

      // Save to context.
      $context['results']['store'] = $store;
      $context['sandbox']['progress']++;
      $context['message'] = t('Created a default store.');
    } catch (\Exception $exception) {
      watchdog_exception('apigee_kickstart', $exception);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function createPaymentGateway(array $values, &$context) {
    try {
      $gateway = \Drupal::entityTypeManager()->getStorage('commerce_payment_gateway')
        ->create($values);
      $gateway->save();

      // Save to context.
      $context['results']['$gateway'] = $gateway;
      $context['sandbox']['progress']++;
      $context['message'] = t('Created a default payment gateway.');
    } catch (\Exception $exception) {
      watchdog_exception('apigee_kickstart', $exception);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function createProducts(array $currencies, &$context) {
    if (isset($context['results']['store'])) {
      $add_credit_products = [];

      /** @var \Apigee\Edge\Api\Monetization\Entity\SupportedCurrencyInterface $currency */
      foreach ($currencies as $currency) {
        try {
          // Create a product variation for this currency.
          $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')
            ->create([
              'type' => 'add_credit',
              'sku' => "ADD-CREDIT-{$currency->getName()}",
              'title' => $currency->getName(),
              'status' => 1,
              'price' => new Price((string) $currency->getMinimumTopUpAmount(), $currency->getId()),
            ]);
          $variation->save();

          // Create an add credit product for this currency.
          $product = \Drupal::entityTypeManager()->getStorage('commerce_product')
            ->create([
              'title' => $currency->getName(),
              'type' => 'add_credit',
              'stores' => [$context['results']['store']],
              'variations' => [$variation],
              AddCreditConfig::ADD_CREDIT_ENABLED_FIELD_NAME => 1,
            ]);
          $product->save();

          $add_credit_products[$currency->getId()] = [
            'product_id' => $product->id(),
          ];

          // Save config.
          \Drupal::configFactory()
            ->getEditable(AddCreditConfig::CONFIG_NAME)
            ->set('products', $add_credit_products)
            ->save();

          $context['sandbox']['progress']++;
          $context['message'] = t('Created the default products.');
        } catch (\Exception $exception) {
          watchdog_exception('apigee_kickstart', $exception);
        }
      }
    }
  }

}
