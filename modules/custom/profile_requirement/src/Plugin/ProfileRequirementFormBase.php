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

namespace Drupal\profile_requirement\Plugin;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base form for profile requirements.
 */
class ProfileRequirementFormBase extends FormBase {

  /**
   * The profile requirement manager.
   *
   * @var \Drupal\profile_requirement\Plugin\ProfileRequirementManagerInterface
   */
  protected $profileRequirementManager;

  /**
   * The profile requirement.
   *
   * @var \Drupal\profile_requirement\Plugin\ProfileRequirementInterface|null
   */
  protected $requirement;

  /**
   * ProfileRequirementFormBase constructor.
   *
   * @param \Drupal\profile_requirement\Plugin\ProfileRequirementManagerInterface $profile_requirement_manager
   *   The profile requirement manager.
   */
  public function __construct(ProfileRequirementManagerInterface $profile_requirement_manager) {
    $this->profileRequirementManager = $profile_requirement_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.profile_requirement')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'profile_requirement_base_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $requirement_id = NULL) {
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Submit'),
      ],
      'cancel' => [
        '#type' => 'link',
        '#title' => $this->t('Cancel'),
        '#attributes' => ['class' => ['button']],
        '#url' => Url::fromRoute('profile_requirement.report'),
      ],
    ];

    /** @var \Drupal\profile_requirement\Plugin\ProfileRequirementInterface $requirement */
    if ($this->requirement = $this->profileRequirementManager->createInstance($requirement_id)) {
      $form = $this->requirement->buildConfigurationForm($form, $form_state);
      $form['#title'] = $this->requirement->getLabel();
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->requirement->submitConfigurationForm($form, $form_state);
  }

}
