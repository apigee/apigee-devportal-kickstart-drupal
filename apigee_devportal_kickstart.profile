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

/**
 * @file
 * Enables modules and site configuration for apigee_devportal_kickstart.
 */

use Drupal\apigee_devportal_kickstart\Installer\ApigeeDevportalKickstartTasksManager;
use Drupal\apigee_devportal_kickstart\Installer\Form\ApigeeMonetizationConfigurationForm;
use Drupal\apigee_devportal_kickstart\Installer\Form\ApigeeEdgeConfigurationForm;
use Drupal\apigee_devportal_kickstart\Installer\Form\DemoInstallForm;
use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Extension\InfoParserException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Implements hook_install_tasks().
 */
function apigee_devportal_kickstart_install_tasks(&$install_state) {
  $tasks = [
    DemoInstallForm::class => [
      'display_name' => t('Install demo content'),
      'type' => 'form',
    ],
    'apigee_devportal_kickstart_theme_setup' => [
      'display_name' => t('Install theme'),
      'display' => FALSE,
    ],
  ];

  // Add monetization tasks if the configured organization is monetizable.
  if (Drupal::moduleHandler()->moduleExists('apigee_edge')
    && Drupal::hasService('address.subdivision_repository')
    && Drupal::hasService('apigee_devportal_kickstart.monetization') && Drupal::service('apigee_devportal_kickstart.monetization')->isMonetizable()) {
    $tasks = array_merge([
      ApigeeMonetizationConfigurationForm::class => [
        'display_name' => t('Configure monetization'),
        'type' => 'form',
      ],
      'apigee_devportal_setup_monetization' => [
        'display_name' => t('Setup monetization'),
        'type' => 'batch',
      ],
    ], $tasks);
  }

  $tasks['apigee_devportal_kickstart_finish'] = [
    'display' => FALSE,
  ];

  return $tasks;
}

/**
 * Implements hook_install_tasks_alter().
 */
function apigee_devportal_kickstart_install_tasks_alter(&$tasks, $install_state) {
  // Do not add the apigee_edge_configure_form tasks if non-interactive install
  // since drush si cannot set default values for the form.
  // Use `drush key-save apigee_edge_connection_default '{\"auth_type\":\"basic\",\"organization\":\"ORGANIZATION\",\"username\":\"USERNAME\",\"password\":\"PASSWORD"}' --key-type=apigee_auth -y`
  // to create a key after drush si.
  if (!$install_state['interactive']) {
    return;
  }

  // Add tasks for configuring Apigee authentication and monetization.
  $apigee_kickstart_tasks = [
    ApigeeEdgeConfigurationForm::class => [
      'display_name' => t('Configure Apigee Edge'),
      'type' => 'form',
    ],
    'apigee_devportal_monetization_preflight' => [],
  ];

  // The task should run before install_configure_form which creates the user.
  $tasks_copy = $tasks;
  $tasks = array_slice($tasks_copy, 0, array_search('install_configure_form', array_keys($tasks))) + $apigee_kickstart_tasks + $tasks_copy;
}

/**
 * Prepares profile for monetization setup.
 *
 * @param array $install_state
 *   The install state.
 */
function apigee_devportal_monetization_preflight(array &$install_state) {
  // The monetization configuration form needs an address field.
  // Enable the address module.
  try {
    \Drupal::service('module_installer')->install(['address']);
  }
  catch (\Exception $exception) {
    watchdog_exception('apigee_kickstart', $exception);
  }
}

/**
 * Install task for setting up monetization and additional modules.
 *
 * @param array $install_state
 *   The install state.
 *
 * @return array
 *   A batch definition.
 */
function apigee_devportal_setup_monetization(array &$install_state) {
  if (isset($install_state['m10n_config']) && ($config = $install_state['m10n_config'])) {
    // Add an operations to install modules.
    $operations = [
      [
        [ApigeeDevportalKickstartTasksManager::class, 'init'],
        [$config]
      ],
      [
        [ApigeeDevportalKickstartTasksManager::class, 'installModules'],
        [$config['modules']],
      ],
    ];

    // Perform additional tasks for apigee_kickstart_m10n_add_credit.
    if (in_array('apigee_kickstart_m10n_add_credit', $config['modules'])) {
      $operations = array_merge($operations, [
        [
          [ApigeeDevportalKickstartTasksManager::class, 'importCurrencies'],
          [$config['supported_currencies']],
        ],
        [
          [ApigeeDevportalKickstartTasksManager::class, 'createStore'],
          [$config['store']],
        ],
        [
          [ApigeeDevportalKickstartTasksManager::class, 'createProducts'],
          [$config['supported_currencies']],
        ],
      ]);
    }

    $batch = [
      'operations' => $operations,
      'title' => t('Setting up monetization'),
      'error_message' => t('The installation has encountered an error.'),
      'progress_message' => t('Completed @current out of @total tasks.'),
    ];

    return $batch;
  }
}

/**
 * Install the theme.
 *
 * @param array $install_state
 *   The install state.
 */
function apigee_devportal_kickstart_theme_setup(array &$install_state) {
  // Clear all status messages generated by modules installed in previous step.
  Drupal::messenger()->deleteByType(MessengerInterface::TYPE_STATUS);

  // Set apigee_kickstart as the default theme.
  \Drupal::configFactory()
    ->getEditable('system.theme')
    ->set('default', 'apigee_kickstart')
    ->save();

  // Ensure that the install profile's theme is used.
  // @see _drupal_maintenance_theme()
  \Drupal::service('theme.manager')->resetActiveTheme();

  // Enable the admin theme for editing content.
  \Drupal::configFactory()
    ->getEditable('node.settings')
    ->set('use_admin_theme', TRUE)
    ->save(TRUE);
}

/**
 * Run any additional tasks for the installation.
 */
function apigee_devportal_kickstart_finish() {
  // Re-run the optional config import again since Drupal installation profile
  // imports optional configuration only once.
  // @see \Drupal\Core\Config\ConfigInstaller::installDefaultConfig
  // @see install_install_profile()
  \Drupal::service('config.installer')->installOptionalConfig();
}

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function apigee_devportal_kickstart_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  $form['#submit'][] = 'apigee_devportal_kickstart_form_install_configure_submit';
}

/**
 * Submission handler to sync the contact.form.feedback recipient.
 */
function apigee_devportal_kickstart_form_install_configure_submit($form, FormStateInterface $form_state) {
  $site_mail = $form_state->getValue('site_mail');
  ContactForm::load('feedback')
    ->setRecipients([$site_mail])
    ->trustData()
    ->save();
}
