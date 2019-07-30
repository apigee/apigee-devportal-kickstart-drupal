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

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\profile_requirement\Annotation\ProfileRequirement;
use Drupal\profile_requirement\Plugin\ProfileRequirementBase;

/**
 * @ProfileRequirement(
 *   id="add_credit_entity_displays",
 *   label="Add credit entity displays",
 *   description="Configure default view displays for add credit products and checkout forms. An add credit product type will be created if none is found.",
 *   severity="recommendation",
 *   action_button_label="Update displays",
 *   weight=100
 * )
 */
class AddCreditEntityDisplays extends ProfileRequirementBase {

  /**
   * The name of the module.
   */
  const MODULE_NAME = 'apigee_kickstart_m10n_add_credit';

  /**
   * An array of config name.
   *
   * @var array
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = [];
    $path = drupal_get_path('module', static::MODULE_NAME) . '/config/optional';
    $file_storage = new FileStorage($path);
    foreach ($file_storage->listAll() as $name) {
      $this->config[$name] = $file_storage->read($name);
    }

    // If the module is installed, we only want all changed optional config.
    if ($this->getModuleHandler()->moduleExists(static::MODULE_NAME)) {
      $this->config = [];
      $storage_comparer = new StorageComparer($file_storage, $this->getConfigStorage(), $this->getConfigManager());
      foreach ($storage_comparer->createChangelist()->getChangelist('update') as $name) {
        $this->config[$name] = $file_storage->read($name);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    // Show help text.
    $form['help'] = [
      '#theme' => 'status_messages',
      '#message_list' => [
        MessengerInterface::TYPE_WARNING => [
          $this->t('Note: This will overwrite the existing configuration.'),
        ],
      ],
      '#weight' => -100,
    ];

    $form['config'] = [
      '#title' => $this->t('Configuration'),
      '#type' => 'checkboxes',
      '#options' => array_combine(array_keys($this->config), array_keys($this->config)),
      '#description' => $this->t('Import and overwrite the following configuration.'),
      '#default_value' => array_keys($this->config),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Enable the apigee_kickstart_add_credit module if it is not enabled.
    $this->getModuleInstaller()->install([static::MODULE_NAME]);

    // Import config.
    $config = array_filter($form_state->getValue('config'));
    foreach ($config as $name) {
      $this->getConfigStorage()->write($name, $this->config[$name]);
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
    return !count($this->config);
  }

}
