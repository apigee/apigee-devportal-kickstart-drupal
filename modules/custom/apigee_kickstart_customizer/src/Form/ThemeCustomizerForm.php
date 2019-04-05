<?php

/**
 * Copyright 2018 Google Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

namespace Drupal\apigee_kickstart_customizer\Form;

use Drupal\apigee_kickstart_customizer\CustomizerInterface;
use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The theme customizer form.
 */
class ThemeCustomizerForm extends ConfigFormBase {

  use AjaxFormHelperTrait;

  /**
   * The theme customizer.
   *
   * @var \Drupal\apigee_kickstart_customizer\CustomizerInterface
   */
  protected $customizer;

  /**
   * ThemeCustomizerForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\apigee_kickstart_customizer\CustomizerInterface $customizer
   *   The theme customizer.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CustomizerInterface $customizer) {
    parent::__construct($config_factory);

    $this->customizer = $customizer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('apigee_kickstart_customizer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'theme_customizer_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $theme = NULL) {
    $form = parent::buildForm($form, $form_state);
    $form['actions']['submit']['#ajax'] = [
      'callback' => '::ajaxSubmit',
    ];
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $values = $this->configFactory->get('customizer.theme.' . $theme)->get('values');

    $form_state->set('theme', $theme);

    $form['farbtastic'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'farbtastic-wrapper',
      ],
    ];

    $theme_config = $this->customizer->getConfig($theme);

    foreach ($theme_config as $group_name => $group) {
      $form[$group_name] = [
        '#type' => 'details',
        '#title' => $group['name'],
        '#open' => TRUE,
      ];

      foreach ($group['variables'] as $name => $title) {
        $form[$group_name][$name] = [
          '#title' => $title,
          '#type' => 'color',
          '#default_value' => $values[$name] ?? NULL,
        ];
      }
    }

    $form['#attached']['library'][] = 'apigee_kickstart_customizer/color';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('customizer.theme.' . $form_state->get('theme'));
    $form_state->cleanValues();
    $values = $form_state->getValues();
    $config->set('values', $values)->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    $command = new RedirectCommand(Url::fromRoute('<front>')->toString());
    $response = new AjaxResponse();
    return $response->addCommand($command);
  }

}
