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

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\PluginBase;

/**
 * Defines a base class for profile requirement plugins.
 */
abstract class ProfileRequirementBase extends PluginBase implements ProfileRequirementInterface {

  use ProfileRequirementTrait;
  use LoggerChannelTrait;
  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function getId(): string {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel(): String {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): String {
    return $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(): ?String {
    return $this->pluginDefinition['form'] ?? ProfileRequirementFormBase::class;
  }

  /**
   * {@inheritdoc}
   */
  public function getActionButtonLabel(): ?String {
    return $this->pluginDefinition['action_button_label'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSeverity(): String {
    return $this->isCompleted() ? 'completed' : ($this->pluginDefinition['severity'] ?? 'warning');
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies(): array {
    return $this->pluginDefinition['dependencies'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getActionButton(): array {
    if (!$this->getActionButtonLabel()) {
      return [];
    }

    return Link::createFromRoute($this->getActionButtonLabel(), "profile_requirement.{$this->getId()}", [
      \Drupal::destination()->getAsArray(),
    ], [
      'attributes' => [
        'data-dialog-type' => 'modal',
        'data-dialog-options' => json_encode([
          'width' => 600,
          'height' => 450,
          'draggable' => FALSE,
          'autoResize' => FALSE,
        ]),
        'class' => [
          'use-ajax',
          'button',
        ],
      ],
    ])->toRenderable();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

}
