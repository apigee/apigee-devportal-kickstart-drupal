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

use Drupal\Core\Form\FormStateInterface;
use Drupal\profile_requirement\Annotation\ProfileRequirement;
use Drupal\profile_requirement\Plugin\ProfileRequirementBase;

/**
 * @ProfileRequirement(
 *   id = "add_credit_product_type",
 *   label = "Add Credit product type",
 *   description = "Configure an add credit product type to handle prepaid balance top ups.",
 *   action_button_label="Create product type",
 *   severity="error"
 * )
 */
class AddCreditProductType extends ProfileRequirementBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['info'] = [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#value' => $this->t('An add credit product type will be created. Are you sure you want to continue?'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->getModuleHandler()->install(['apigee_kickstart_m10n_add_credit']);
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
    // Check if we have an add credit product type.
    return count($this->getEntityTypeManager()
      ->getStorage('commerce_product_type')
      ->loadByProperties([
        'third_party_settings.apigee_m10n_add_credit.apigee_m10n_enable_add_credit' => TRUE,
      ]));
  }

}
