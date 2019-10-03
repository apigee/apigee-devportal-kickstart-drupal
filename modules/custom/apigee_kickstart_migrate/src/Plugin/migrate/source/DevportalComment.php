<?php

/**
 * Copyright 2019 Google Inc.
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

namespace Drupal\apigee_kickstart_migrate\Plugin\migrate\source;

use Drupal\comment\Plugin\migrate\source\d7\Comment;
use Drupal\migrate\Row;

/**
 * Migrate source for Drupal 7 comment with custom fields.
 *
 * @MigrateSource(
 *   id = "devportal_comment"
 * )
 */
class DevportalComment extends Comment {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    $query->innerJoin('users', 'u', 'c.uid = u.uid');
    $query->addField('u', 'mail', 'user_mail');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $comment_type = $row->getSourceProperty('node_type') === 'forum' ? 'comment_forum' : 'comment';
    $row->setSourceProperty('comment_type_name', $comment_type);
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = parent::fields();
    $fields['comment_type_name'] = $this->t('The comment type.');
    $fields['user_mail'] = $this->t('The email of the user.');
    return $fields;
  }

}
