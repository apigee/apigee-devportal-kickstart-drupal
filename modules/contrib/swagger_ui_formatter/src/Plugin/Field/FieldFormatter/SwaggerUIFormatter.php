<?php

namespace Drupal\swagger_ui_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation of the 'swagger_ui' formatter.
 *
 * @FieldFormatter(
 *   id = "swagger_ui",
 *   label = @Translation("Swagger UI"),
 *   description = @Translation("Formats file fields with Swagger YAML or JSON files with a rendered Swagger UI"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class SwaggerUIFormatter extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'validator' => 'default',
      'validator_url' => '',
      'doc_expansion' => 'list',
      'show_top_bar' => FALSE,
      'sort_tags_by_name' => FALSE,
      'supported_submit_methods' => [
        'get' => 'get',
        'put' => 'put',
        'post' => 'post',
        'delete' => 'delete',
        'options' => 'options',
        'head' => 'head',
        'patch' => 'patch'
      ],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['validator'] = [
      '#type' => 'select',
      '#title' => $this->t("Validator"),
      '#description' => $this->t("Default=Swagger.io's online validator, None= No validation, Custom=Provide a custom validator url"),
      '#default_value' => $this->getSetting('validator'),
      '#options' => [
        'none' => $this->t('None'),
        'default' => $this->t('Default'),
        'custom' => $this->t('Custom'),
      ],
    ];
    $form['validator_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Validator URL"),
      '#description' => $this->t("The custom validator url to be used to validated the swagger files."),
      '#default_value' => $this->getSetting('validator_url'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][validator]"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $form['doc_expansion'] = [
      '#type' => 'select',
      '#title' => $this->t("Doc Expansion"),
      '#description' => $this->t("Controls how the API listing is displayed."),
      '#default_value' => $this->getSetting('doc_expansion'),
      '#options' => [
        'none' => $this->t('None - Expands nothing'),
        'list' => $this->t('List - Expands only tags'),
        'full' => $this->t('Full - Expands tags and operations'),
      ],
    ];
    $form['show_top_bar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Show Top Bar"),
      '#description' => $this->t("Controls whether the Swagger UI top bar should be displayed or not."),
      '#default_value' => $this->getSetting('show_top_bar'),
    ];
    $form['sort_tags_by_name'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Sort tags by name"),
      '#description' => $this->t("Controls whether the tag groups should be ordered alphabetically or not."),
      '#default_value' => $this->getSetting('sort_tags_by_name'),
    ];
    $form['supported_submit_methods'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t("Try it out support for HTTP Methods"),
      '#description' => $this->t("List of HTTP methods that have the Try it out feature enabled. Selecting none disables Try it out for all operations. This does not filter the operations from the display."),
      '#default_value' => $this->getSetting('supported_submit_methods'),
      '#options' => [
        'get' => $this->t('GET'),
        'put' => $this->t('PUT'),
        'post' => $this->t('POST'),
        'delete' => $this->t('DELETE'),
        'options' => $this->t('OPTIONS'),
        'head' => $this->t('HEAD'),
        'patch' => $this->t('PATCH'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $supported_submit_methods = array_filter($this->getSetting('supported_submit_methods'));
    $summary[] = $this->t('Uses %validator validator, Doc Expansion of %doc_expansion, Shows top bar: %show_top_bar, Tags sorted by name: %sort_tags_by_name, Try it out support for HTTP Methods: %supported_submit_methods.', [
      '%validator' => $this->getSetting('validator'),
      '%doc_expansion' => $this->getSetting('doc_expansion'),
      '%show_top_bar' => $this->getSetting('show_top_bar') ? $this->t('Yes') : $this->t('No'),
      '%sort_tags_by_name' => $this->getSetting('sort_tags_by_name') ? $this->t('Yes') : $this->t('No'),
      '%supported_submit_methods' => !empty($supported_submit_methods) ? implode(', ', array_map('strtoupper', $supported_submit_methods)) : $this->t('None'),
    ]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $elements = parent::view($items, $langcode);

    if (_swagger_ui_formatter_get_library_path()) {
      $swagger_files = [];
      /** @var \Drupal\file\Entity\File $file */
      foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
        $swagger_files[] = file_create_url($file->getFileUri());
      }

      $elements['#attached'] = ['library' => [
        'swagger_ui_formatter/swagger_ui_formatter.swagger_ui',
        'swagger_ui_formatter/swagger_ui_formatter.swagger_ui_integration',
      ]];
      $elements['#attached']['drupalSettings']['swaggerUIFormatter'][$this->fieldDefinition->getName()] = [
        'swaggerFiles' => $swagger_files,
        'validator' => $this->getSetting('validator'),
        'validatorUrl' => $this->getSetting('validator_url'),
        'docExpansion' => $this->getSetting('doc_expansion'),
        'showTopBar' => $this->getSetting('show_top_bar'),
        'sortTagsByName' => $this->getSetting('sort_tags_by_name'),
        'supportedSubmitMethods' => array_keys(array_filter($this->getSetting('supported_submit_methods'))),
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $element[$delta] = [
        '#theme' => 'swagger_ui_field_item',
        '#field_name' => $this->fieldDefinition->getName(),
        '#delta' => $delta,
      ];
    }
    return $element;
  }

}
