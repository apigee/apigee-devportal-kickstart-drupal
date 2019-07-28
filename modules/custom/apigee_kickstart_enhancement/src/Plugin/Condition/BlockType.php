<?php

/**
 * @file
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

namespace Drupal\apigee_kickstart_enhancement\Plugin\Condition;

use Drupal\block\BlockInterface;
use Drupal\block_content\BlockContentUuidLookup;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Block type' condition.
 *
 * @Condition(
 *   id = "block_type",
 *   label = @Translation("Block types"),
 * )
 */
class BlockType extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The uuid lookup service.
   *
   * @var \Drupal\block_content\BlockContentUuidLookup
   */
  protected $uuidLookup;

  /**
   * Creates a new BlockType instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\block_content\BlockContentUuidLookup $uuid_lookup
   *   The uuid lookup service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, BlockContentUuidLookup $uuid_lookup) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->uuidLookup = $uuid_lookup;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('block_content.uuid_lookup')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'uuid' => NULL,
        'bundles' => [],
        'is_adjacent' => NULL,
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\block\BlockInterface $block */
    if (!($block = $form_state->getFormObject()->getEntity())) {
      // Do nothing if we do not have a block.
      return [];
    }

    $options = [];
    $block_types = $this->entityTypeManager->getStorage('block_content_type')
      ->loadMultiple();
    foreach ($block_types as $type) {
      $options[$type->id()] = $type->label();
    }

    $form['bundles'] = [
      '#title' => $this->t('Block types'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#description' => $this->t('Hide this block if another block of the selected types is visible in the same region.'),
      '#default_value' => $this->configuration['bundles'],
    ];

    $form['is_adjacent'] = [
      '#title' => $this->t('Hide if block is next to each other'),
      '#type' => 'checkbox',
      '#description' => $this->t('This will hide the block only if the selected block types is next to it.'),
      '#default_value' => $this->configuration['is_adjacent'],
    ];

    // Store the block uuid so that we can retrieve it on evaluate.
    $form['uuid'] = [
      '#type' => 'value',
      '#value' => $block->uuid(),
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['uuid'] = $form_state->getValue('uuid');
    $this->configuration['bundles'] = array_filter($form_state->getValue('bundles'));
    $this->configuration['is_adjacent'] = $form_state->getValue('is_adjacent');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['bundles']) && !$this->isNegated()) {
      return TRUE;
    }

    $block_storage = $this->entityTypeManager->getStorage('block');
    if (($uuid = $this->configuration['uuid']) && ($current_block = $block_storage->loadByProperties(['uuid' => $uuid]))) {
      /** @var \Drupal\block\BlockInterface $current_block */
      $current_block = reset($current_block);

      // Find all blocks within the same region and theme.
      /** @var \Drupal\block\BlockInterface[] $blocks */
      if ($blocks = $block_storage->loadByProperties([
        'theme' => $current_block->getTheme(),
        'region' => $current_block->getRegion(),
      ])) {
        // Find all other visible blocks.
        unset($blocks[$current_block->id()]);
        $blocks = array_filter($blocks, function(BlockInterface $block) {
          return $block->access('view');
        });

        if ($this->configuration['is_adjacent']) {
          // Add the current block and sort the blocks by weight.
          $blocks[$current_block->id()] = $current_block;
          uasort($blocks, 'Drupal\block\Entity\Block::sort');

          // Find adjacent blocks.
          $block_ids = array_keys($blocks);
          $key = (array_search($current_block->id(), $block_ids));
          $blocks = array_slice($blocks, $key === 0 ? 0 : $key - 1, 2 + $key);
          unset($blocks[$current_block->id()]);
        }

        // Check if we have a block content of configured type.
        $block_content_storage = $this->entityTypeManager->getStorage('block_content');
        foreach ($blocks as $block) {
          if (($plugin = $block->getPlugin())
            && ($plugin->getPluginDefinition()['provider'] == 'block_content')
            && ($id = $this->uuidLookup->get($plugin->getDerivativeId()))
            && ($block_content = $block_content_storage->load($id))) {
            if (!empty($this->configuration['bundles'][$block_content->bundle()])) {
              return FALSE;
            }
          }
        }
      }
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    // TODO: Implement summary() method.
  }

}
