<?php

/*
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

namespace Drupal\apigee_devportal_kickstart\Plugin\ProfileRequirement;

use Apigee\Edge\Api\Management\Controller\OrganizationController;
use Apigee\Edge\Api\Monetization\Controller\OrganizationProfileController;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use Drupal\address\FieldHelper;
use Drupal\address\LabelHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\profile_requirement\Annotation\ProfileRequirement;
use Drupal\profile_requirement\Plugin\ProfileRequirementBase;

/**
 * @ProfileRequirement(
 *   id = "commerce_store",
 *   label = "Commerce store",
 *   description = "Setup a commerce store to handle prepaid balance checkouts.",
 *   action_button_label="Setup store",
 *   severity="error",
 *   dependencies= {
 *      "apigee_edge_connection",
 *   }
 * )
 */
class CommerceStore extends ProfileRequirementBase {

  /**
   * The Apigee Organization.
   *
   * @var \Apigee\Edge\Api\Monetization\Entity\OrganizationProfileInterface
   */
  protected $organization;

  /**
   * CommerceStore constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    try {
      $organization_id = $this->getApigeeEdgeSdkConnector()->getOrganization();
      $client = $this->getApigeeEdgeSdkConnector()->getClient();

      // TODO: Figure out if we need to cache these values here.
      // This happens only once during installation. We probably do not need to
      // cache these values?
      $organization_controller = new OrganizationController($client);
      $organization = $organization_controller->load($organization_id);
      /** @var \Apigee\Edge\Api\Management\Entity\OrganizationInterface $organization */
      if ($organization->getPropertyValue('features.isMonetizationEnabled') === 'true') {
        // Set the organization.
        $organization_profile_controller = new OrganizationProfileController($organization_id, $client);
        $this->organization = $organization_profile_controller->load($organization_id);
      }
    } catch (\Exception $exception) {
      watchdog_exception('apigee_kickstart', $exception);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['store'] = [
      '#markup' => $this->t('Create a store for handling prepaid balance top ups.'),
    ];

    $site_config = $this->getConfigFactory()->get('system.site');
    $form['name'] = [
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
      '#placeholder' => $this->t('Name of store'),
      '#default_value' => "{$site_config->get('name')} store",
    ];

    $form['mail'] = [
      '#title' => $this->t('Email'),
      '#type' => 'email',
      '#placeholder' => $this->t('admin@example.com'),
      '#default_value' => $site_config->get('mail'),
      '#description' => $this->t('Store email notifications are sent from this address.'),
    ];

    $currencies = $this->getEntityTypeManager()->getStorage('commerce_currency')->loadMultiple();
    $currency_codes = array_keys($currencies);
    $form['default_currency'] = [
      '#type' => 'select',
      '#title' => $this->t('Default currency'),
      '#options' => array_combine($currency_codes, $currency_codes),
    ];

    $form['type'] = [
      '#type' => 'value',
      '#value' => 'online',
    ];

    $form['address'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Address'),
    ];

    $form['address']['country_code'] = [
      '#title' => $this->t('Country'),
      '#type' => 'address_country',
      '#required' => TRUE,
      '#default_value' => $this->getConfigFactory()->get('system.date')->get('country.default'),
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
      $form['address'][FieldHelper::getPropertyName($address_field)] = [
        '#title' => $labels[$address_field],
        '#type' => 'textfield',
        '#size' => $settings['size'],
        '#placeholder' => $settings['placeholder'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();

    try {
      $values = $form_state->getValues();
      $store = $this->getEntityTypeManager()->getStorage('commerce_store')
        ->create($values);
      $store->save();
    }
    catch (\Exception $exception) {
      watchdog_exception('apigee_kickstart', $exception);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(): bool {
    return $this->getModuleHandler()->moduleExists('apigee_m10n_add_credit');
  }

  /**
   * {@inheritdoc}
   */
  public function isCompleted(): bool {
    return count($this->getEntityTypeManager()->getStorage('commerce_store')->loadMultiple());
  }

}
