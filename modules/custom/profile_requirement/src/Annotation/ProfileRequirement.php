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

namespace Drupal\profile_requirement\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a profile requirement annotation object.
 *
 * @Annotation
 */
class ProfileRequirement extends Plugin {

  /**
   * The ID of the plugin.
   *
   * @var string
   */
  public $id;

  /**
   * The label for the plugin.
   *
   * @var string
   */
  public $label;

  /**
   * The description for the plugin.
   *
   * @var string
   */
  public $description;

  /**
   * The form for the plugin.
   *
   * @var string
   */
  public $form;

  /**
   * The label for the form button.
   *
   * @var string
   */
  public $action_button_label;

  /**
   * The severity of the requirement.
   *
   * @var string
   */
  public $severity;

  /**
   * The weight of the plugin for sorting.
   *
   * @var int
   */
  public $weight;

  /**
   * An array of dependent profile requirement Ids.
   *
   * @var array
   */
  public $dependencies;

}
