<?php

/**
 * Copyright 2020 Google Inc.
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

namespace Drupal\Tests\apigee_kickstart_search\Kernel;

use Drupal\search\Form\SearchBlockForm;
use Drupal\Tests\token\Kernel\KernelTestBase;

/**
 * Tests the search_block_form form.
 */
class SearchBlockFormTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'search',
    'search_api_db',
    'apigee_kickstart_search',
  ];

  /**
   * Tests the search form.
   */
  public function testSearchForm() {
    $form = $this->container->get('form_builder')->getForm(SearchBlockForm::class);

    $this->assertEqual('/search', $form['#action']);
  }

}
