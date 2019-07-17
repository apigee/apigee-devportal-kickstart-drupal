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
use Drupal\Core\Form\FormStateInterface;

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
