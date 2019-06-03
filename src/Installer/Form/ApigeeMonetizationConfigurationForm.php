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
use Apigee\Edge\Api\Monetization\Controller\OrganizationProfileController;
use Apigee\Edge\Api\Monetization\Controller\SupportedCurrencyController;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;
use CommerceGuys\Intl\Currency\CurrencyRepository;
use Drupal\address\FieldHelper;
use Drupal\address\LabelHelper;
use Drupal\apigee_edge\SDKConnectorInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form for Apigee Monetization.
 */
class ApigeeMonetizationConfigurationForm extends FormBase {

  /**
   * SDK connector service.
   *
   * @var \Drupal\apigee_edge\SDKConnectorInterface
   */
  protected $sdkConnector;

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
   * The subdivision repository.
   *
   * @var \CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface
   */
  protected $subdivisionRepository;

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
   * ApigeeM10nConfigurationForm constructor.
   *
   * @param \Drupal\apigee_edge\SDKConnectorInterface $sdk_connector
   *   SDK connector service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface $subdivision_repository
   *   The subdivision repository.
   */
  public function __construct(SDKConnectorInterface $sdk_connector, LanguageManagerInterface $language_manager, SubdivisionRepositoryInterface $subdivision_repository) {
    $this->sdkConnector = $sdk_connector;
    $this->languageManager = $language_manager;
    $this->subdivisionRepository = $subdivision_repository;
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
      $container->get('apigee_edge.sdk_connector'),
      $container->get('language_manager'),
      $container->get('address.subdivision_repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apigee_devportal_kickstart_m10n_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Some messages stick around for installation tasks. Clear them all.
    $this->messenger()->deleteAll();

    $form['#title'] = $this->t('Configure monetization');

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

    // Create individual address fields.
    // We cannot use the address field because #ajax forms won't work in the
    // installer.
    $form['store']['address'] = [
      '#type' => 'tree',
    ];

    $form['store']['address']['country_code'] = [
      '#title' => $this->t('Country'),
      '#type' => 'address_country',
      '#required' => TRUE,
    ];

    $address_fields = [
      AddressField::ADDRESS_LINE1 => [
        'size' => 60,
        'placeholder' => 'Acme Street',
      ],
      AddressField::ADDRESS_LINE2 => [
        'size' => 60,
        'placeholder' => '',
      ],
      AddressField::LOCALITY => [
        'size' => 30,
        'placeholder' => 'Santa Clara',
      ],
      AddressField::ADMINISTRATIVE_AREA => [
        'size' => 30,
        'placeholder' => 'CA or California',
      ],
      AddressField::POSTAL_CODE => [
        'size' => 10,
        'placeholder' => '95050',
      ],
    ];
    $labels = LabelHelper::getGenericFieldLabels();
    foreach ($address_fields as $address_field => $settings) {
      $form['store']['address'][FieldHelper::getPropertyName($address_field)] = [
        '#title' => $labels[$address_field],
        '#type' => 'textfield',
        '#size' => $settings['size'],
        '#placeholder' => $settings['placeholder'],
      ];
    }

    // Add default address from organization.
    if ($addresses = $this->organization->getAddresses()) {
      /** @var \Apigee\Edge\Api\Monetization\Structure\Address $address */
      $address = reset($addresses);

      $form['store']['address']['country_code']['#default_value'] = $address->getCountry();
      $form['store']['address'][FieldHelper::getPropertyName(AddressField::ADDRESS_LINE1)]['#default_value'] = $address->getAddress1();
      $form['store']['address'][FieldHelper::getPropertyName(AddressField::ADDRESS_LINE1)]['#default_value'] = $address->getAddress1();
      $form['store']['address'][FieldHelper::getPropertyName(AddressField::LOCALITY)]['#default_value'] = $address->getCity();
      $form['store']['address'][FieldHelper::getPropertyName(AddressField::POSTAL_CODE)]['#default_value'] = $address->getZip();

      // Find the state code from the country subdivisions.
      if (($state = $address->getState())
        && ($subdivisions = $this->subdivisionRepository->getList([$address->getCountry()]))
        && (in_array($state, $subdivisions)) || (isset($subdivisions[$state]))) {
        $form['store']['address'][FieldHelper::getPropertyName(AddressField::ADMINISTRATIVE_AREA)]['#default_value'] = $state;
      }
    }

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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Validate the state.
    if (!$this->getStateCode($form_state)) {
      $form_state->setErrorByName('address][' . FieldHelper::getPropertyName(AddressField::ADMINISTRATIVE_AREA), $this->t('Please enter a valid administrative area.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    $values = $form_state->getValues();
    $values['modules'] = array_keys(array_filter($values['modules']));

    if (count($values['modules'])) {
      // Update the supported currencies.
      if (isset($values['supported_currencies'])) {
        $supported_currencies = array_keys(array_filter($values['supported_currencies']));
        $values['supported_currencies'] = [];
        foreach ($supported_currencies as $currency_code) {
          $values['supported_currencies'][$currency_code] = $this->supportedCurrencies[strtolower($currency_code)];
        }
      }

      // Convert state to state code.
      $values['store']['address'][FieldHelper::getPropertyName(AddressField::ADMINISTRATIVE_AREA)] = $this->getStateCode($form_state);

      // Save to install state.
      $buildInfo = $form_state->getBuildInfo();
      $buildInfo['args'][0]['m10n_config'] = $values;
      $form_state->setBuildInfo($buildInfo);
    }
  }

  /**
   * Helper to get the state code from form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return string
   *   The state code. eg. CA.
   */
  protected function getStateCode(FormStateInterface $form_state): String {
    if (($store = $form_state->getValue('store'))
      && ($address = $store['address'])
      && ($property_name = FieldHelper::getPropertyName(AddressField::ADMINISTRATIVE_AREA))
      && isset($address[$property_name])
      && ($state = $address[$property_name])
      && ($subdivisions = $this->subdivisionRepository->getList([$address['country_code']]))
      && (isset($subdivisions[strtoupper($state)]) || ($state = array_search($state, $subdivisions)))
    ) {
      return $state;
    }

    return FALSE;
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

  /**
   * Helper to get importable currencies.
   *
   * @return array|\CommerceGuys\Intl\Currency\Currency[]
   *   An array of importable currencies.
   */
  protected function getImportableCurrencies(): array {
    $language = $this->languageManager->getConfigOverrideLanguage() ?: $this->languageManager->getCurrentLanguage();
    return $this->currencyRepository->getAll($language->getId());
  }

}