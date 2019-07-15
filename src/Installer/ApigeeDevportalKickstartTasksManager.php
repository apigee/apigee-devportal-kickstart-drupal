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
use Drupal\commerce_price\Price;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Defines a service for performing additional tasks in batch.
 */
class ApigeeDevportalKickstartTasksManager implements ApigeeDevportalKickstartTasksManagerInterface {

  /**
   * {@inheritdoc}
   */
  public static function init(array $config, array &$context) {
    $context['message'] = t('Preparing setup...');
  }

  /**
   * {@inheritdoc}
   */
  public static function installModules(array $modules, array &$context) {
    try {
      \Drupal::service('module_installer')->install($modules);
      $context['message'] = t('Installed monetization modules.');
    }
    catch (\Exception $exception) {
      watchdog_exception('apigee_kickstart', $exception);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function importCurrencies(array $currencies, array &$context) {
    try {
      foreach ($currencies as $currency) {
        // Import the currency.
        \Drupal::service('commerce_price.currency_importer')->import($currency->getName());
      }

      $context['message'] = t('Imported supported currencies.');
    }
     catch (ServiceNotFoundException $exception) {
       watchdog_exception('apigee_kickstart', $exception);
     }
  }

  /**
   * {@inheritdoc}
   */
  public static function createStore(array $values, array &$context) {
    try {
      $store = \Drupal::entityTypeManager()->getStorage('commerce_store')
        ->create($values);
      $store->save();

      // Save to context.
      $context['results']['store'] = $store;
      $context['message'] = t('Created a default store.');
    }
    catch (\Exception $exception) {
      watchdog_exception('apigee_kickstart', $exception);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function createPaymentGateway(array $values, array &$context) {
    try {
      $gateway = \Drupal::entityTypeManager()->getStorage('commerce_payment_gateway')
        ->create($values);
      $gateway->save();

      // Save to context.
      $context['results']['$gateway'] = $gateway;
      $context['message'] = t('Created a default payment gateway.');
    }
    catch (\Exception $exception) {
      watchdog_exception('apigee_kickstart', $exception);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function createProducts(array $currencies, array &$context) {
    if (isset($context['results']['store'])) {
      $add_credit_products = [];

      /** @var \Apigee\Edge\Api\Monetization\Entity\SupportedCurrencyInterface $currency */
      foreach ($currencies as $currency) {
        try {
          $minimum_amount = (string) $currency->getMinimumTopUpAmount();
          $currency_code = $currency->getName();
          // Create a product variation for this currency.
          /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
          $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')
            ->create([
              'type' => 'add_credit',
              'sku' => "ADD-CREDIT-{$currency->getName()}",
              'title' => $currency->getName(),
              'status' => 1,
              'price' => new Price($minimum_amount, $currency_code),
            ]);
          $variation->set('apigee_price_range', [
            'minimum' => $minimum_amount,
            'maximum' => 999,
            'default' => $minimum_amount,
            'currency_code' => $currency_code,
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

          $context['message'] = t('Created default products.');
        }
        catch (\Exception $exception) {
          watchdog_exception('apigee_kickstart', $exception);
        }
      }
    }
  }

}
