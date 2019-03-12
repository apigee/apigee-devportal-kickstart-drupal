<?php

/**
 * Copyright 2018 Google Inc.
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

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Installer form to optionally enable apigee_kickstart Demo.
 */
class ApigeeDemoInstallerForm extends FormBase {

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * Constructs a ApigeeDemoInstallerForm.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
   */
  public function __construct(ModuleInstallerInterface $module_installer) {
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apigee_demo_installer_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#title'] = t('Demo content');
    $form['organization_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Organization Name'),
      '#size' => 60,
      '#maxlength' => 128,
    ];
    $form['user_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User Name'),
      '#size' => 60,
      '#maxlength' => 128,
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#size' => 60,
      '#maxlength' => 128,
    ];
    $form['enable'] = [
      '#type' => 'checkbox',
      '#title' => 'Enable demo content',
      '#description' => t("Creates some demo content to help you test out Apigee portal features quickly."),
      '#default_value' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#weight' => 15,
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $enable = $form_state->getValue('enable');

    // We have to use ->getUserInput() to supercede->getValue() because that
    // isn't correctly set when passing the form value to drush si like:
    // "drush si apigee_kickstart apigee_demo_installer_form.enable=0".
    $input = $form_state->getUserInput();
    if (isset($input['enable'])) {
      $enable = !empty($input['enable']);
    }

    if ($enable) {
      $this->moduleInstaller->install(['apigee_default_content']);
      $config = \Drupal::configFactory()->getEditable('system.site');
      $config->set('page.front', '/node/2')->save();
      $this->apigeeUpdateEntityAlias();
    }
    $organization_name = !empty($form_state->getValue('organization_name')) ? $form_state->getValue('organization_name') : '';
    $user_name = !empty($form_state->getValue('user_name')) ? $form_state->getValue('user_name') : '';
    $password = !empty($form_state->getValue('password')) ? $form_state->getValue('password') : '';
    if (!empty($organization_name) || !empty($user_name) || !empty($password)) {
      $folder = 'public://apigee/';
      if (!file_exists($folder)) {
        mkdir($folder, 0775, TRUE);
      }
      $uri = 'public://apigee/config.txt';
      $absolute_path = \Drupal::service('file_system')->realpath($uri);
      $handle = fopen($absolute_path, 'w');
      fwrite($handle, $organization_name . "\n");
      fwrite($handle, $user_name . "\n");
      fwrite($handle, $password . "\n");
      fclose($handle);
    }
  }

  /**
   * Update the entity aliases.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   not used
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function apigeeUpdateEntityAlias() {
    $entities = [];
    $entities['node'] = \Drupal::entityQuery('node')->execute();
    $entities['user'] = \Drupal::entityQuery('user')->execute();
    $entities['taxonomy_term'] = \Drupal::entityQuery('taxonomy_term')
      ->execute();
    $result = [];

    foreach ($entities as $type => $entity_list) {
      foreach ($entity_list as $entity_id) {
        $result[] = [
          'entity_type' => $type,
          'id' => $entity_id,
        ];
      }
    }

    // Use the sandbox to store the information needed to track progression.
    if (!isset($sandbox['current'])) {
      // The count of entities visited so far.
      $sandbox['current'] = 0;
      // Total entities that must be visited.
      $sandbox['max'] = count($result);
      // A place to store messages during the run.
    }

    // Process entities by groups of 20.
    // When a group is processed, the batch update engine determines
    // whether it should continue processing in the same request or provide
    // progress feedback to the user and wait for the next request.
    $limit = 20;
    $result = array_slice($result, $sandbox['current'], $limit);

    foreach ($result as $row) {
      $entity_storage = \Drupal::entityTypeManager()
        ->getStorage($row['entity_type']);
      $entity = $entity_storage->load($row['id']);

      // Update Entity URL alias.
      \Drupal::service('pathauto.generator')
        ->updateEntityAlias($entity, 'update');

      // Update our progress information.
      $sandbox['current']++;
    }

    $sandbox['#finished'] = empty($sandbox['max']) ? 1 : ($sandbox['current'] / $sandbox['max']);

    if ($sandbox['#finished'] >= 1) {
      return t('The batch URL Alias update is finished.');
    }

  }

}
