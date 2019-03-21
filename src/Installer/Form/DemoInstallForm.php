<?php

/**
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

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Installer task for Apigee Developer Portal Kickstart Demo Content.
 */
class DemoInstallForm extends FormBase {

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * DemoInstallForm constructor.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ModuleInstallerInterface $module_installer, ConfigFactoryInterface $config_factory) {
    $this->moduleInstaller = $module_installer;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apigee_devportal_kickstart_demo_install_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = $this->t('Install Demo content?');

    $form['install_demo_content'] = [
      '#type' => 'checkbox',
      '#title' => 'Enable demo content',
      '#description' => $this->t('Check this box to install some demo content to help you test out Apigee portal features quickly.'),
      '#default_value' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $demo_content = $form_state->getValue('install_demo_content');

    // We have to use getUserInput() to supersede getValue() because that isn't
    // correctly set when passing the form value to Drush si like:
    // "drush si apigee_kickstart apigee_devportal_kickstart_demo_install_form.install_demo_content=0".
    $input = $form_state->getUserInput();
    if (isset($input['install_demo_content'])) {
      $demo_content = !empty($input['install_demo_content']);
    }

    if ($demo_content) {
      $this->moduleInstaller->install(['apigee_kickstart_content']);

      // Set the front page to node 1.
      $this->configFactory->getEditable('system.site')
        ->set('page.front', '/node/1')
        ->save();
    }
  }

}
