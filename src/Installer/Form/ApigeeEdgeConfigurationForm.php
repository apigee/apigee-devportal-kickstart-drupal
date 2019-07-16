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

use Drupal\apigee_edge\Form\AuthenticationForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Configuration form for Apigee Edge.
 */
class ApigeeEdgeConfigurationForm extends AuthenticationForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Add a form title for the installer.
    $form['#title'] = $this->t('Configure Apigee Edge');

    // Show help text.
    $form['help'] = [
      '#theme' => 'status_messages',
      '#message_list' => [
        MessengerInterface::TYPE_WARNING => [
          $this->t('Note: Your connection settings are going to be saved in Drupal\'s configuration system. If you wish to do this later or <a href=":url" target="_blank">use a different key provider</a>, you may skip this step.', [
            ':url' => 'https://www.drupal.org/docs/8/modules/apigee-edge/configure-the-connection-to-apigee-edge',
          ]),
        ],
      ],
      '#weight' => -100,
    ];

    // Add a skip this step button.
    $form['actions']['skip'] = [
      '#type' => 'submit',
      '#value' => $this->t('Skip this step'),
      '#submit' => [[$this, 'skipStepSubmit']],
      '#name' => 'skip',
      '#limit_validation_errors' => [],
    ];

    // Add a custom after_build callback.
    $form['#after_build'][] = '::formAfterBuild';

    $form['#attached']['library'][] = 'apigee_devportal_kickstart/apigee_edge_form';

    return $form;
  }

  /**
   * Custom after_build callback for the form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form array.
   */
  public static function formAfterBuild(array $form, FormStateInterface $form_state) {
    // Hide the provider and test connection section.
    // These won't work anyway since AJAX forms cannot work on the installer.
    $form['settings']['provider_section']['#access'] = FALSE;
    $form['settings']['test_connection']['#access'] = FALSE;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Do validation if not skip button.
    if ((string) $form_state->getValue('op') !== (string) $form['actions']['skip']['#value']) {
      parent::validateForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Clear error messages.
    $this->messenger()->deleteByType(MessengerInterface::TYPE_ERROR);

    parent::submitForm($form, $form_state);
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

}
