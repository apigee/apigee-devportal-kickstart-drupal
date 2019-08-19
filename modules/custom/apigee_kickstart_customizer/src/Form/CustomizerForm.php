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
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The theme customizer form.
 */
class CustomizerForm extends ConfigFormBase {

  use AjaxFormHelperTrait;

  /**
   * The theme customizer.
   *
   * @var \Drupal\apigee_kickstart_customizer\CustomizerInterface
   */
  protected $customizer;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config_factory;

  /**
   * The redirect destination.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $destination;

  /**
   * CustomizerForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $destination
   *   The redirect destination.
   * @param \Drupal\apigee_kickstart_customizer\CustomizerInterface $customizer
   *   The theme customizer.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RedirectDestinationInterface $destination, CustomizerInterface $customizer) {
    parent::__construct($config_factory);

    $this->config_factory = $config_factory;
    $this->destination = $destination;
    $this->customizer = $customizer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('redirect.destination'),
      $container->get('apigee_kickstart_customizer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'customizer_form';
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
    $form_state->set('theme', $theme);

    $form['actions']['submit']['#ajax'] = [
      'callback' => '::ajaxSubmit',
    ];

    // Add a wrapper for farbtastic.
    $form['farbtastic'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'farbtastic-wrapper',
      ],
    ];

    $form['farbtastic_help'] = [
      '#type' => 'html_tag',
      '#tag' => 'small',
      '#value' => $this->t('Click on a color field below and use the color wheel to pick a color.'),
      '#attributes' => [
        'class' => [
          'farbtastic-help',
        ],
      ],
    ];

    $values = $this->configFactory->get('apigee_kickstart_customizer.theme.' . $theme)->get('values');
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
          '#type' => 'pseudo_color',
          '#default_value' => $values[$name] ?? NULL,
          '#attributes' => [
            'data-color' => $values[$name],
            'data-picker' => TRUE,
          ],
        ];
      }
    }

    $form['#attached']['library'][] = 'apigee_kickstart_customizer/form';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    $values = $form_state->getValues();
    $theme = $form_state->get('theme');
    $this->configFactory->getEditable('apigee_kickstart_customizer.theme.' . $theme)
      ->set('values', $values)->save();

    $this->customizer->updateStylesheetForTheme($theme);

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    $command = new RedirectCommand(Url::fromUserInput($this->destination->get())->toString());
    $response = new AjaxResponse();
    return $response->addCommand($command);
  }

}
