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

namespace Drupal\apigee_kickstart_customizer\Element;

use Drupal\Core\Render\Element\Color;

/**
 * Provides a peudo form element for choosing a color.
 *
 * By default \Drupal\Core\Render\Element\Color will render the input as a
 * HTML 5 color field. This triggers a native color picker.
 *
 * The pseudo color field acts like a default input with color constraints.
 *
 * Properties:
 * - #default_value: Default value, in a format like #ffffff.
 *
 * Example usage:
 * @code
 * $form['color'] = array(
 *   '#type' => 'pseudo_color',
 *   '#title' => $this->t('Color'),
 *   '#default_value' => '#ffffff',
 * );
 * @endcode
 *
 * @FormElement("pseudo_color")
 */
class PseudoColor extends Color {

  /**
   * {@inheritdoc}
   */
  public static function preRenderColor($element) {
    $element = parent::preRenderColor($element);

    // Use a text input type.
    $element['#attributes']['type'] = 'text';

    return $element;
  }

}
