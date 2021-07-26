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
use Apigee\Edge\Api\ApigeeX\Controller\SupportedCurrencyController as ApigeeXSupportedCurrencyController;
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
   * An array of missing dependencies.
   *
   * @var array
   */
  protected $missingDependencies;

  /**
   * TRUE if organization is monetizable ApigeeX.
   *
   * @var bool
   */
  protected $isOrgApigeeX;

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
  public function __construct(SDKConnectorInterface $sdk_connector, LanguageManagerInterface $language_manager, SubdivisionRepositoryInterface $subdivision_repository = NULL) {
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
      if ($organization && ('CLOUD' === $organization->getRuntimeType() || 'HYBRID' === $organization->getRuntimeType())) {
        if ($this->isMonetizable = $organization->getAddonsConfig()->getMonetizationConfig()->getEnabled() === TRUE) {
          // Set the organization.
          $this->organization = $organization;

          // Set supported currencies.
          $supported_currency_controller = new ApigeeXSupportedCurrencyController($organization_id, $client);
          $this->supportedCurrencies = $supported_currency_controller->getEntities();

          $this->isOrgApigeeX = TRUE;
        }
      }
      /** @var \Apigee\Edge\Api\Management\Entity\OrganizationInterface $organization */
      elseif ($this->isMonetizable = $organization->getPropertyValue('features.isMonetizationEnabled') === 'true') {
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
      $this->messenger()->addError($exception->getMessage());
    }

    // Check for missing dependencies.
    $this->missingDependencies = \Drupal::service('apigee_devportal_kickstart.monetization')->getMissingDependencies();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('apigee_edge.sdk_connector'),
      $container->get('language_manager')
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
    // We do this check here instead of the constructor so that it can be
    // initialized on every form rebuild.
    if (!$this->subdivisionRepository && \Drupal::hasService('address.subdivision_repository')) {
      $this->subdivisionRepository = \Drupal::service('address.subdivision_repository');
    }

    $error_messages = [];
    $form['#title'] = $this->t('Configure monetization');

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#submit' => [[$this, 'skipStepSubmit']],
      '#validate' => [],
      '#limit_validation_errors' => [],
    ];

    // Check if the organization profile could not be loaded.
    if (!$this->organization) {
      $error_messages[MessengerInterface::TYPE_ERROR][] = $this->t('The organization profile could not be loaded. You can continue to the next step and manually setup monetization later.');
    }
    else {
      // Check for dependencies.
      if (!empty($this->missingDependencies)) {
        $error_messages[MessengerInterface::TYPE_ERROR][] = $this->t('The following modules are required to enable monetization: <strong>@missing</strong>.', [
          '@missing' => implode(', ', $this->missingDependencies),
        ]);

        $form['help'] = [
          '#type' => 'inline_template',
          '#template' => '
            <ol>
              <li>{{ "Run the following command to install the missing modules:"|t }} <code>composer require {% for dependency in dependencies %}drupal/{{dependency}} {% endfor %}</code></li>
              <li>{{ "Then reload this page to continue setting up monetization."|t }}</li>
            </ol>
            <p>{{ "If you do not wish to enable monetization now, click continue."|t }}</p>
          ',
          '#context' => [
            'dependencies' => $this->missingDependencies,
          ],
        ];
      }

    }

    // Show error messages and continue.
    if (count($error_messages)) {
      $form['message'] = [
        '#theme' => 'status_messages',
        '#message_list' => $error_messages,
        '#weight' => -1000,
      ];

      $form['skip_validation'] = [
        '#type' => 'value',
        '#value' => TRUE,
      ];

      // Add a submit button that skips validation.
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#submit' => [[$this, 'skipStepSubmit']],
        '#button_type' => 'primary',
        '#validate' => [],
        '#limit_validation_errors' => [],
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

    $form['modules']['apigee_m10n_add_credit'] = [
      '#title' => $this->t('Enable Add Credit module'),
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
          'input[name="modules[apigee_m10n_add_credit]"]' => ['checked' => TRUE],
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
          'input[name="modules[apigee_m10n_add_credit]"]' => ['checked' => TRUE],
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
          'input[name="modules[apigee_m10n_add_credit]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['store']['default_currency'] = [
      '#type' => 'value',
      '#value' => $this->isOrgApigeeX ? 'USD' : $this->organization->getCurrencyCode(),
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

    // Check if not ApigeeX and add default address from organization.
    if (!$this->isOrgApigeeX && $addresses = $this->organization->getAddresses()) {
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

    $form['currencies'] = [
      '#type' => 'details',
      '#title' => $this->t('Currencies'),
      '#open' => TRUE,
      '#description' => $this->t('Create a product to add credit for the following supported currencies.'),
      '#states' => [
        'visible' => [
          'input[name="modules[apigee_m10n]"]' => ['checked' => TRUE],
          'input[name="modules[apigee_m10n_add_credit]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    if (empty($this->supportedCurrencies)) {
      $form['currencies']['help'] = [
        '#theme' => 'status_messages',
        '#message_list' => [
          MessengerInterface::TYPE_ERROR => [
            $this->t('Please setup your Apigee organization supported currencies as described in the <a href="@docs" target="_blank">Apigee Docs</a>. Then reload this page to automate the creation of "Add credit" products.', [
              '@docs' => 'https://docs.apigee.com/api-platform/monetization/manage-supported-currencies#ui',
            ]),
          ],
        ],
      ];
    }
    else {
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

    $form['payment_gateway'] = [
      '#type' => 'details',
      '#title' => $this->t('Payment Gateway'),
      '#description' => $this->t('A payment gateway is needed during checkout for the "Add Credit" module.'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          'input[name="modules[apigee_m10n]"]' => ['checked' => TRUE],
          'input[name="modules[apigee_m10n_add_credit]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['payment_gateway']['gateway'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create a test payment gateway'),
      '#description' => $this->t('Create a manual payment gateway (useful for tests).'),
      '#default_value' => TRUE,
    ];

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
    if ($form_state->getValue('skip_validation')) {
      return;
    }

    parent::validateForm($form, $form_state);

    // Validate the state for given country.
    if (($state = $this->getAddressPropertyValueFromFormState($form_state, FieldHelper::getPropertyName(AddressField::ADMINISTRATIVE_AREA)))
      && ($country_code = $this->getAddressPropertyValueFromFormState($form_state, 'country_code'))
      && !$this->validateStateCode($state, $country_code)
    ) {
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
      $supported_currencies = empty($values['supported_currencies']) ? [] : array_keys(array_filter($values['supported_currencies']));
      $values['supported_currencies'] = [];
      foreach ($supported_currencies as $currency_code) {
        $values['supported_currencies'][$currency_code] = $this->supportedCurrencies[strtolower($currency_code)];
      }

      // Convert state to state code.
      $property_name = FieldHelper::getPropertyName(AddressField::ADMINISTRATIVE_AREA);
      $values['store']['address'][$property_name] = $this->getAddressPropertyValueFromFormState($form_state, $property_name);

      // Save to install state.
      $buildInfo = $form_state->getBuildInfo();
      $buildInfo['args'][0]['m10n_config'] = $values;
      $form_state->setBuildInfo($buildInfo);
    }
  }

  /**
   * Helper to get the value of an address property from form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param string $property_name
   *   The property name.
   *
   * @return mixed
   *   The value of the given property.
   */
  protected function getAddressPropertyValueFromFormState(FormStateInterface $form_state, string $property_name): string {
    if (($store = $form_state->getValue('store'))
      && ($address = $store['address'])
      && isset($address[$property_name])
      && ($value = $address[$property_name])
    ) {
      return $value;
    }

    return FALSE;
  }

  /**
   * Validates a state for the given country.
   *
   * @param string $state
   *   The state code or name. eg. CA or California.
   * @param string $country_code
   *   The country code. eg. US.
   *
   * @return bool
   *   TRUE if valid state for given country. Otherwise FALSE.
   */
  protected function validateStateCode($state, $country_code) {
    if ($subdivisions = $this->subdivisionRepository->getList([$country_code])) {
      return isset($subdivisions[strtoupper($state)]) || ($state = array_search($state, $subdivisions));
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
