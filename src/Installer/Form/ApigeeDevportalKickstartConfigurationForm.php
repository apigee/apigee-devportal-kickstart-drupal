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

namespace Drupal\apigee_devportal_kickstart\Installer\Form;

use Apigee\Edge\Api\Management\Controller\OrganizationController;
use Apigee\Edge\Api\Management\Entity\OrganizationInterface;
use Apigee\Edge\Api\Monetization\Controller\OrganizationProfileController;
use Apigee\Edge\Api\Monetization\Controller\SupportedCurrencyController;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\FieldOverride;
use CommerceGuys\Intl\Currency\CurrencyRepository;
use Drupal\apigee_edge\Form\AuthenticationForm;
use Drupal\apigee_edge\SDKConnectorInterface;
use Drupal\apigee_kickstart_m10n_add_credit\AddCreditConfig;
use Drupal\commerce_price\CurrencyImporterInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\MissingDependencyException;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\key\KeyRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form for Apigee Monetization.
 */
class ApigeeDevportalKickstartConfigurationForm extends FormBase {

  /**
   * The factory for configuration objects.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * SDK connector service.
   *
   * @var \Drupal\apigee_edge\SDKConnectorInterface
   */
  protected $sdkConnector;

  /**
   * The Drupal module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The library's currency repository.
   *
   * @var \CommerceGuys\Intl\Currency\CurrencyRepository
   */
  protected $currencyRepository;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Apigee Organization.
   *
   * @var \Apigee\Edge\Api\Monetization\Entity\OrganizationProfileInterface
   */
  protected $organization;

  /**
   * An array of supported currencies.
   *
   * @var \Apigee\Edge\Api\Monetization\Entity\SupportedCurrencyInterface[]
   */
  protected $supportedCurrencies;

  /**
   * TRUE if organization is monetizable.
   *
   * @var bool
   */
  protected $isMonetizable;

  /**
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepository;

  /**
   * ApigeeM10nConfigurationForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\apigee_edge\SDKConnectorInterface $sdk_connector
   *   SDK connector service.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The Drupal module installer.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\key\KeyRepositoryInterface $key_repository
   *   The key repository.
   */
  public function __construct(ConfigFactoryInterface $config_factory, SDKConnectorInterface $sdk_connector, ModuleInstallerInterface $module_installer, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, KeyRepositoryInterface $key_repository) {
    $this->configFactory = $config_factory;
    $this->sdkConnector = $sdk_connector;
    $this->moduleInstaller = $module_installer;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->keyRepository = $key_repository;
    $this->currencyRepository = new CurrencyRepository();

    try {
      $organization_id = $this->sdkConnector->getOrganization();
      $client = $this->sdkConnector->getClient();

      // TODO: Figure out if we need to cache these values here.
      // This happens only once during installation. We probably do not need to
      // cache these values?
      $organization_controller = new OrganizationController($client);
      $organization = $organization_controller->load($organization_id);
      /** @var \Apigee\Edge\Api\Management\Entity\OrganizationInterface $organization */
      if ($this->isMonetizable = $organization->getPropertyValue('features.isMonetizationEnabled') === 'true') {
        // Set the organization.
        $organization_profile_controller = new OrganizationProfileController($organization_id, $client);
        $this->organization = $organization_profile_controller->load($organization_id);

        // Set supported currencies.
        $supported_currency_controller = new SupportedCurrencyController($organization_id, $client);
        $this->supportedCurrencies = $supported_currency_controller->getEntities();
      }
    }
    catch (\Exception $exception) {
      watchdog_exception('apigee_kickstart', $exception);
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('apigee_edge.sdk_connector'),
      $container->get('module_installer'),
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('key.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apigee_m10n_configuration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Some messages stick around for installation tasks. Clear them all.
    $this->messenger()->deleteAll();

    $form['#title'] = $this->t('Configure Apigee Kickstart');

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#submit' => [[$this, 'skipStepSubmit']],
      '#validate' => [],
      '#limit_validation_errors' => [],
    ];

    // If monetization is not enabled, show a message and continue.
    if (!$this->isMonetizable) {
      $form['message'] = [
        '#theme' => 'status_messages',
        '#message_list' => [
          MessengerInterface::TYPE_WARNING => [
            $this->t('Monetization is not enabled for your organization'),
          ],
        ],
      ];

      return $form;
    }

    $form['modules'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $form['modules']['apigee_m10n'] = [
      '#title' => $this->t('Enable monetization'),
      '#type' => 'checkbox',
      '#description' => $this->t('Enable monetization for your Apigee Edge organization.'),
    ];

    $form['modules']['apigee_kickstart_m10n_add_credit'] = [
      '#title' => $this->t('Enable prepaid balance top up'),
      '#type' => 'checkbox',
      '#description' => $this->t('Allow users to add credit to their prepaid balances.'),
      '#states' => [
        'visible' => [
          'input[name="modules[apigee_m10n]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['store'] = [
      '#type' => 'details',
      '#title' => $this->t('Store'),
      '#open' => TRUE,
      '#tree' => TRUE,
      '#description' => $this->t('Create a store for handling prepaid balance top ups.'),
      '#states' => [
        'visible' => [
          'input[name="modules[apigee_m10n]"]' => ['checked' => TRUE],
          'input[name="modules[apigee_kickstart_m10n_add_credit]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $site_config = $this->configFactory()->get('system.site');
    $form['store']['name'] = [
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
      '#placeholder' => $this->t('Name of store'),
      '#default_value' => $site_config->get('name'),
      '#states' => [
        'required' => [
          'input[name="modules[apigee_kickstart_m10n_add_credit]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['store']['mail'] = [
      '#title' => $this->t('Email'),
      '#type' => 'email',
      '#placeholder' => $this->t('admin@example.com'),
      '#default_value' => $site_config->get('mail'),
      '#description' => $this->t('Store email notifications are sent from this address.'),
      '#states' => [
        'required' => [
          'input[name="modules[apigee_kickstart_m10n_add_credit]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['store']['default_currency'] = [
      '#type' => 'value',
      '#value' => $this->organization->getCurrencyCode(),
    ];

    $form['store']['type'] = [
      '#type' => 'value',
      '#value' => 'online',
    ];

    $form['store']['address'] = [
      '#title' => $this->t('Address'),
      '#type' => 'address',
      '#field_overrides' => [
        AddressField::GIVEN_NAME => FieldOverride::HIDDEN,
        AddressField::FAMILY_NAME => FieldOverride::HIDDEN,
        AddressField::ORGANIZATION => FieldOverride::HIDDEN,
        AddressField::ADDRESS_LINE2 => FieldOverride::HIDDEN,
        AddressField::POSTAL_CODE => FieldOverride::OPTIONAL,
      ],
    ];

    if (count($this->supportedCurrencies)) {
      $form['currencies'] = [
        '#type' => 'details',
        '#title' => $this->t('Currencies'),
        '#open' => TRUE,
        '#description' => $this->t('Create a product to add credit for the following supported currencies.'),
        '#states' => [
          'visible' => [
            'input[name="modules[apigee_m10n]"]' => ['checked' => TRUE],
            'input[name="modules[apigee_kickstart_m10n_add_credit]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $currency_options = [];
      $importable_currencies = $this->getImportableCurrencies();
      foreach ($this->supportedCurrencies as $currency) {
        if ($currency->getStatus() === 'ACTIVE' && isset($importable_currencies[$currency->getName()])) {
          $currency_options[$currency->getName()] = "{$currency->getDisplayName()} ({$currency->getName()})";
        }
      }

      $form['currencies']['supported_currencies'] = [
        '#type' => 'checkboxes',
        '#options' => $currency_options,
        '#default_value' => array_keys($currency_options),
      ];
    }

    // Add default address from organization.
    if ($addresses = $this->organization->getAddresses()) {
      /** @var \Apigee\Edge\Api\Monetization\Structure\Address $address */
      $address = reset($addresses);

      $form['store']['address']['#default_value'] = [
        'address_line1' => $address->getAddress1(),
        'locality' => $address->getCity(),
        'administrative_area' => $address->getState(),
        'country_code' => $address->getCountry(),
        'postal_code' => $address->getZip(),
      ];
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#button_type' => 'primary',
    ];

    // Add a skip this step button.
    $form['actions']['skip'] = [
      '#type' => 'submit',
      '#value' => $this->t('Skip this step'),
      '#submit' => [[$this, 'skipStepSubmit']],
      '#validate' => [],
      '#limit_validation_errors' => [],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    global $install_state;
    $form_state->cleanValues();
    $install_state['m10n_config'] = $form_state->getValues();

    // Update the supported currencies.
    if (isset($install_state['m10n_config']['supported_currencies'])) {
      foreach ($install_state['m10n_config']['supported_currencies'] as $currency_code) {
        $install_state['m10n_config']['supported_currencies'][$currency_code] = $this->supportedCurrencies[strtolower($currency_code)];
      }
    }

    // Add values for a payment gateway.
    // TODO: Figure out if this should be configurable in the form.
    $install_state['m10n_config']['gateway'] = [
      'id' => 'default',
      'label' => 'Default',
      'plugin' => 'manual',
    ];
  }

//  public function installModules(FormStateInterface $form_state, &$context) {
//    if ($modules = array_keys(array_filter($form_state->getValue('modules')))) {
//      // Enable the apigee_kickstart_m10n module also.
//      // This holds all default config for m10n.
//      $modules[] = 'apigee_kickstart_m10n';
//
//      // Enable the modules.
//      try {
//        $this->moduleInstaller->install($modules);
//      } catch (MissingDependencyException $exception) {
//        watchdog_exception('apigee_kickstart', $exception);
//      }
//    }
//
//    if (!isset($context['sandbox']['progress'])) {
//      $context['sandbox']['progress'] = 0;
//    }
//
//    $context['sandbox']['progress']++;
//    $context['message'] = $this->t('Installed monetization modules');
//  }

//  public function importCurrencies(FormStateInterface $form_state, &$context) {
//    if ($currencies = array_filter($form_state->getValue('supported_currencies'))) {
//      /** @var CurrencyImporterInterface $currency_importer */
//      // This cannot be injected because this has to be run after the required
//      // modules is installed.
//      $currency_importer = \Drupal::service('commerce_price.currency_importer');
//
//      foreach ($currencies as $currency_code) {
//        // Import the currency.
//        $currency_importer->import($currency_code);
//
//        // Save it to context.
//        $context['results']['currencies'][$currency_code] = $this->supportedCurrencies[strtolower($currency_code)];
//      }
//    }
//
//    $context['sandbox']['progress']++;
//    $context['message'] = $this->t('Imported supported currencies');
//  }

//  public function createStore(FormStateInterface $form_state, &$context) {
//    // Create a store.
//    $store = $this->entityTypeManager->getStorage('commerce_store')
//      ->create($form_state->getValue('store'));
//    $store->save();
//
//    // Create a payment gateway.
//    $this->entityTypeManager->getStorage('commerce_payment_gateway')->create([
//      'id' => 'default',
//      'label' => 'Default',
//      'plugin' => 'manual',
//    ])->save();
//
//    // Save to context.
//    $context['results']['store'] = $store;
//
//    $context['sandbox']['progress']++;
//    $context['message'] = $this->t('Created a default store and payment gateway');
//  }

  public function createProducts(FormStateInterface $form_state, &$context) {
    // If we have currencies and a store, create products.
    if (count($context['results']['currencies']) && isset($context['results']['store'])) {
      $add_credit_products = [];

      /** @var \Apigee\Edge\Api\Monetization\Entity\SupportedCurrencyInterface $currency */
      foreach ($context['results']['currencies'] as $currency) {
        // Create a product variation for this currency.
        $variation = $this->entityTypeManager->getStorage('commerce_product_variation')
          ->create([
            'type' => 'add_credit',
            'sku' => "ADDCREDIT-{$currency->getName()}",
            'title' => $currency->getName(),
            'status' => 1,
            'price' => new Price((string) $currency->getMinimumTopUpAmount(), $currency->getId()),
          ]);
        $variation->save();

        // Create an add credit product for this currency.
        $product = $this->entityTypeManager->getStorage('commerce_product')
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
      }

      // Save config.
      $this->configFactory()
        ->getEditable(AddCreditConfig::CONFIG_NAME)
        ->set('products', $add_credit_products)
        ->save();
    }

    $context['sandbox']['progress']++;
    $context['message'] = $this->t('Created the default products');
  }

  /**
   * Provides a submit handler for the skip step button.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function skipStepSubmit(array $form, FormStateInterface $form_state) {
    global $install_state;
    $install_state['completed_task'] = install_verify_completed_task();
  }

  protected function getImportableCurrencies() {
    $language = $this->languageManager->getConfigOverrideLanguage() ?: $this->languageManager->getCurrentLanguage();
    return $this->currencyRepository->getAll($language->getId());
  }

  protected function isMonetizable(OrganizationInterface $organization) {
    // We can only check for monetization if we have a valid organization and
    // a key configured.
    if ($organization && ($active_key_id = $this->configFactory->get(AuthenticationForm::CONFIG_NAME)->get('active_key')) && ($this->keyRepository->getKey($active_key_id))) {
      return $organization->getPropertyValue('features.isMonetizationEnabled') === 'true';
    }
    return FALSE;
  }

}
